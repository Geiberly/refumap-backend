<?php echo json_encode(app('App\Models\User')->where('role', 'admin')->get(['id', 'email', 'status'])->toArray());
