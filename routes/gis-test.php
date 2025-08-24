<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GisController;

Route::get('/gis/test', function () {
    try {
        // Test database connection
        DB::connection()->getPdo();
        
        // Test spatial function
        $result = DB::select("SELECT ST_SRID(ST_GeomFromText('POINT(1 1)')) as srid");
        
        return [
            'database' => 'Connected successfully',
            'spatial' => $result[0]->srid === 0 ? 'Spatial functions working' : 'Spatial functions not working as expected',
            'version' => DB::select('SELECT VERSION() as version')[0]->version
        ];
    } catch (\Exception $e) {
        return [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
    }
});
