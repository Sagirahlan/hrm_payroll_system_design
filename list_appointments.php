foreach(App\Models\AppointmentType::all() as $t) { echo $t->id . ': ' . $t->name . PHP_EOL; }
