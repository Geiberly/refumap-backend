<?php
echo json_encode(App\Models\CitizenReport::selectRaw("status, count(*) as c")->groupBy("status")->get());
