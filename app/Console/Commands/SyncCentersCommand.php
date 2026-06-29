<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MapPoint;
use App\Models\Category;
use Illuminate\Support\Facades\Http;

class SyncCentersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:centers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync help centers from external API (ayudaparavenezuela.com)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting sync from ayudaparavenezuela.com API...');

        // Get category IDs
        $acopioCategory = Category::where('slug', 'centro-acopio')->first();
        $refugioCategory = Category::where('slug', 'refugio')->first();

        if (!$acopioCategory || !$refugioCategory) {
            $this->error('Required categories (centro-acopio, refugio) not found in the database. Please run CategoriesSeeder first.');
            return 1;
        }

        $urls = [
            'centers' => 'https://ayudaparavenezuela.com/api/public/centers/csv',
            'help_points' => 'https://ayudaparavenezuela.com/api/public/help-points/csv'
        ];

        $totalSynced = 0;

        foreach ($urls as $type => $url) {
            $this->info("Fetching CSV for {$type} from {$url}...");
            
            try {
                $response = Http::timeout(30)->get($url);
                
                if (!$response->successful()) {
                    $this->error("Failed to fetch data from {$url}. Status: " . $response->status());
                    continue;
                }

                $csvData = $response->body();
                $rows = explode("\n", trim($csvData));
                
                if (count($rows) < 2) {
                    $this->warn("No data rows found in {$url}.");
                    continue;
                }

                // Parse header
                $header = str_getcsv(array_shift($rows));
                $headerMap = array_flip($header);
                
                $requiredColumns = ['id', 'name', 'latitude', 'longitude'];
                foreach ($requiredColumns as $col) {
                    if (!isset($headerMap[$col])) {
                        $this->error("Required column '{$col}' not found in CSV from {$url}. Skipping.");
                        continue 2;
                    }
                }

                $syncedForUrl = 0;

                foreach ($rows as $line) {
                    if (empty(trim($line))) continue;
                    
                    $row = str_getcsv($line);
                    
                    if (count($row) !== count($header)) {
                        continue;
                    }

                    $data = [];
                    foreach ($header as $index => $columnName) {
                        $data[$columnName] = $row[$index] ?? null;
                    }

                    // Map CSV fields to map_points fields
                    $externalId = $data['id'];
                    $name = $data['name'];
                    
                    // Address combination
                    $addressParts = array_filter([$data['address'] ?? null, $data['city'] ?? null, $data['state'] ?? null, $data['country'] ?? null]);
                    $address = implode(', ', $addressParts);

                    $latitude = $data['latitude'];
                    $longitude = $data['longitude'];
                    $phone = $data['phone'] ?? null;
                    
                    // Combine notes, schedule, supplies, volunteers
                    $notesParts = [];
                    if (!empty($data['organization'])) $notesParts[] = "Organización: " . $data['organization'];
                    if (!empty($data['schedule'])) $notesParts[] = "Horario: " . $data['schedule'];
                    if (!empty($data['supply_types'])) $notesParts[] = "Insumos aceptados: " . str_replace('|', ', ', $data['supply_types']);
                    if (!empty($data['accepts_volunteers'])) $notesParts[] = "Acepta voluntarios: " . ($data['accepts_volunteers'] === 'true' ? 'Sí' : 'No');
                    if (!empty($data['notes'])) $notesParts[] = "Notas: " . $data['notes'];
                    $notes = implode("\n", $notesParts);
                    $notes .= "\n(ID Externo: {$externalId})";

                    $isActive = ($data['is_active'] ?? 'true') === 'true';

                    $currentCategory = ($type === 'help_points') ? $refugioCategory : $acopioCategory;

                    // Update or create based on exact external ID in notes or latitude/longitude match
                    // But simpler: just try to match by name and lat/long rounded to 4 decimals, or rely on updateOrCreate with lat/long.
                    // We'll match by name to avoid duplicate creations.
                    $mapPoint = MapPoint::updateOrCreate(
                        [
                            'name' => mb_substr($name, 0, 255),
                        ],
                        [
                            'category_id' => $currentCategory->id,
                            'address' => mb_substr($address, 0, 255),
                            'latitude' => $latitude,
                            'longitude' => $longitude,
                            'contact_phone' => mb_substr($phone, 0, 30),
                            'notes' => $notes,
                            'status' => $isActive ? 'verified' : 'closed',
                            'source' => 'official',
                            'description' => mb_substr("Importado desde ayudaparavenezuela.com. " . ($data['organization'] ?? ''), 0, 500)
                        ]
                    );

                    $syncedForUrl++;
                    $totalSynced++;
                }

                $this->info("Successfully synced {$syncedForUrl} points from {$type}.");

            } catch (\Exception $e) {
                $this->error("Error processing {$url}: " . $e->getMessage());
            }
        }

        $this->info("Sync complete! Total points synced: {$totalSynced}");
        return 0;
    }
}
