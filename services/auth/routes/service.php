<?php 

use App\Models\Permission;


$filePath = storage_path('framework/route/dynamic_routes.php');

// Ensure the directory exists
$directory = dirname($filePath);
if (!is_dir($directory)) {
    mkdir($directory, 0777, true);
}

// Ensure the file exists, then create it
if (!file_exists($filePath)) {
    file_put_contents($filePath, "");
}

if (file_exists($filePath) && trim(file_get_contents($filePath)) == "") {
    $routes = Permission::all()->map(function ($permission) {
        if(($permission->method) && ($permission->service!='auth')){ 
            return "Route::" . $permission->method . "('" . str_replace('api/v1', '', $permission->uri) . "', function () {
               return  callservice('" . $permission->service . "');
            })->name('" . str_replace('api.', '', $permission->name) . "');";
        }
    })->implode("\n");
    
    // Save the route definitions to a file
    file_put_contents($filePath, "<?php\n" . $routes);
}




if (file_exists($filePath)) {
    include $filePath;
}

