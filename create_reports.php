<?php

App\Models\CitizenReport::create([
  "report_type" => "road_blocked",
  "title" => "Vía bloqueada por escombros",
  "description" => "Avenida principal cerrada por escombros caídos de la construcción.",
  "status" => "pending",
  "latitude" => 10.4736,
  "longitude" => -66.8070,
  "address" => "Avenida Tamanaco, Caracas",
]);

App\Models\CitizenReport::create([
  "report_type" => "danger_zone",
  "title" => "Zona inundada cerca del puente",
  "description" => "El río se desbordó y no hay paso.",
  "status" => "verified",
  "latitude" => 10.4700,
  "longitude" => -66.8000,
  "address" => "Puente Río Seco",
]);

App\Models\CitizenReport::create([
  "report_type" => "lack_of_supplies",
  "title" => "Falta agua en el refugio",
  "description" => "Llevamos 3 días sin servicio.",
  "status" => "rejected",
  "latitude" => 10.4750,
  "longitude" => -66.8100,
  "address" => "Refugio Escuela Bolivariana",
]);

echo "Reports generated.\n";
