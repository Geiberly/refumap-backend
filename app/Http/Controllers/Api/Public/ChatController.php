<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\MapPoint;
use App\Models\AdmittedPerson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'messages' => 'required|array',
            'messages.*.role' => 'required|in:user,assistant,system',
            'messages.*.content' => 'required|string',
        ]);

        $userMessages = $request->input('messages');

        // Extract context data from DB
        // Extract context data from DB
        $refugiosActivos = MapPoint::where('category_id', 1)->where('status', 'active')->count();
        $hospitalesActivos = MapPoint::where('category_id', 2)->where('status', 'active')->count();
        $viasBloqueadas = MapPoint::where('category_id', 8)->where('status', 'active')->count();
        $admittedPeople = \App\Models\AdmittedPerson::count();

        // 1. Geolocalización (Haversine)
        $nearestRefugio = null;
        $nearestPeligro = null;
        $coords = $request->input('coords');
        if ($coords && isset($coords['lat']) && isset($coords['lng'])) {
            $lat = (float) $coords['lat'];
            $lng = (float) $coords['lng'];

            $haversine = "(6371 * acos(cos(radians($lat)) * cos(radians(latitude)) * cos(radians(longitude) - radians($lng)) + sin(radians($lat)) * sin(radians(latitude))))";

            $nearestRefugio = MapPoint::selectRaw("*, {$haversine} AS distance")
                ->where('category_id', 1)->where('status', 'active')->orderBy('distance')->first();

            $nearestPeligro = MapPoint::selectRaw("*, {$haversine} AS distance")
                ->where('category_id', 7)->where('status', 'active')->orderBy('distance')->first();
        }

        $geoInfo = "";
        if ($nearestRefugio) $geoInfo .= "El refugio más cercano al usuario es '{$nearestRefugio->title}' a " . round($nearestRefugio->distance, 1) . " km. ";
        if ($nearestPeligro) $geoInfo .= "¡ATENCIÓN! La zona peligrosa más cercana es '{$nearestPeligro->title}' a " . round($nearestPeligro->distance, 1) . " km.";

        // 2. Detección de Búsqueda de Personas (Cédula o Nombre)
        $lastMsg = end($userMessages);
        $cedulaInfo = "";
        if ($lastMsg && $lastMsg['role'] === 'user') {
            $searchQuery = null;
            $isCedula = false;

            if (preg_match('/\b\d{7,8}\b/', $lastMsg['content'], $matches)) {
                $searchQuery = $matches[0];
                $isCedula = true;
            } elseif (preg_match('/(?:nombre(?: de)?|llama|busco a|cédula|cedula|ci)\s+([A-Za-z]+(?:\s+[A-Za-z]+)?)/i', $lastMsg['content'], $matches)) {
                $searchQuery = trim($matches[1]);
                // Evitar palabras comunes
                if (strlen($searchQuery) < 3 || in_array(strtolower($searchQuery), ['alguien', 'persona', 'un', 'una', 'el', 'la'])) {
                    $searchQuery = null;
                }
            }

            if ($searchQuery) {
                // Buscar localmente primero
                $localQuery = \App\Models\AdmittedPerson::with('hospital');
                if ($isCedula) {
                    $localQuery->where('cedula', 'LIKE', '%' . $searchQuery);
                } else {
                    $localQuery->where('full_name', 'LIKE', '%' . $searchQuery . '%');
                }
                
                $localResults = $localQuery->take(5)->get();

                if ($localResults->count() == 1) {
                    $person = $localResults->first();
                    $hName = $person->hospital ? $person->hospital->name : ($person->hospital_name_snapshot ?: 'un centro de atención');
                    $cedulaInfo = "INFO PRIVADA: El usuario busca a '$searchQuery'. ¡SÍ está registrada en RefuMap! Su nombre es {$person->full_name} y está siendo atendida en el centro médico: {$hName}. Dile esto al usuario mencionando explícitamente el nombre del centro médico.";
                } elseif ($localResults->count() > 1) {
                    $cedulaInfo = "INFO PRIVADA: El usuario busca a '$searchQuery'. Encontramos " . $localResults->count() . " personas con ese nombre en RefuMap. Dile al usuario que hay varias coincidencias e invítalo a ingresar a la sección 'Búsqueda de Personas' para ver la lista completa y detallada.";
                } else {
                    // Fallback a API Externa
                    try {
                        $extResponse = \Illuminate\Support\Facades\Http::timeout(3)
                            ->get("https://localizadosvenezuela.com/api/v1/localizados?q=" . urlencode($searchQuery) . "&limit=5");
                        
                        if ($extResponse->successful()) {
                            $extData = $extResponse->json();
                            $extList = isset($extData['data']) ? $extData['data'] : (is_array($extData) ? $extData : []);
                            
                            if (count($extList) == 1) {
                                $extPerson = $extList[0];
                                $extName = $extPerson['nombreCompleto'] ?? 'Persona encontrada';
                                $extLocation = $extPerson['lugarNombre'] ?? 'la red nacional (localizadosvenezuela.com)';
                                $cedulaInfo = "INFO PRIVADA: El usuario busca a '$searchQuery'. No está en RefuMap, pero ¡SÍ fue encontrada en la red nacional externa! Nombre: $extName. Ubicación: $extLocation. ¡Dale esta excelente noticia!";
                            } elseif (count($extList) > 1) {
                                $cedulaInfo = "INFO PRIVADA: El usuario busca a '$searchQuery'. Encontramos " . count($extList) . " personas con ese nombre en la Red Nacional. Dile al usuario que hay varias coincidencias e invítalo a ingresar a la sección 'Búsqueda de Personas' para ver la lista completa con más info.";
                            } else {
                                $cedulaInfo = "INFO PRIVADA: El usuario busca a '$searchQuery'. NO está en nuestros sistemas ni en la red externa actualmente.";
                            }
                        }
                    } catch (\Exception $e) {
                        // Ignore
                    }
                }
            }
        }

        // System prompt context
        $systemPromptText = "Eres el asistente de RefuMap Venezuela. Eres extremadamente EMPÁTICO, AMABLE, CÁLIDO y MUY ACTIVO. Habla como un humano protector.
DATOS EN VIVO: Refugios activos: $refugiosActivos | Hospitales: $hospitalesActivos | Vías bloqueadas: $viasBloqueadas | Personas a salvo: $admittedPeople.
$geoInfo
$cedulaInfo

REGLAS DE INTERACCIÓN:
1. Si te saludan, saluda con muchísima calidez y ofrece ayuda.
2. Si preguntan estadísticas (cuántos refugios, etc): Diles la cifra pero acompáñala de un comentario humano y cálido (Ej. '¡Actualmente tenemos 8 refugios activos listos para ayudar!').
3. Si preguntan por el refugio más cercano o zonas de peligro: USA LA INFORMACIÓN DE UBICACIÓN provista arriba (si la hay). Diles a cuántos kilómetros exactos está el refugio y adviérteles de la zona peligrosa. Si no tienes datos de ubicación, diles amablemente que por favor acepten el permiso de GPS en su navegador.
4. Si preguntan por una cédula o nombre: USA LA INFO PRIVADA provista arriba. Si encontraste a la persona, dale las buenas noticias con mucha empatía y emoción. Si no está registrada, dale ánimos y dile que intente buscar por nombre en la sección 'Búsqueda de Personas'.
5. Sismos: Da 3 pasos cortos con tono calmado: Agáchate, Cúbrete, Sujétate.
6. NO parezcas un robot, NO leas las reglas en voz alta. Usa tus propias palabras.";

        // Preparamos el payload en el formato que exige Gemini API
        $geminiContents = [];
        foreach ($userMessages as $msg) {
            $geminiRole = $msg['role'] === 'assistant' ? 'model' : 'user';
            $geminiContents[] = [
                'role' => $geminiRole,
                'parts' => [['text' => $msg['content']]]
            ];
        }

        $payload = [
            'system_instruction' => [
                'parts' => ['text' => $systemPromptText]
            ],
            'contents' => $geminiContents
        ];

        try {
            $apiKey = env('GEMINI_API_KEY');
            $response = Http::timeout(20)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", $payload);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No recibí respuesta.';
                return response()->json([
                    'message' => $reply
                ]);
            }

            return response()->json([
                'error' => 'Error en la API de Gemini', 
                'details' => $response->body()
            ], 500);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error de conexión con la IA', 'details' => $e->getMessage()], 500);
        }
    }
}
