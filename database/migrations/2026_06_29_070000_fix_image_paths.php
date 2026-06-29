<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fetch all image records that contain a storage URL prefix before a data URI
        $rows = DB::table('product_images')
            ->where('image_path', 'like', "%storage/data:image%")
            ->orWhere('image_path', 'like', "%http://%/storage/data:image%")
            ->get();

        foreach ($rows as $row) {
            $path = $row->image_path;
            $pos = strpos($path, 'data:image');
            if ($pos !== false) {
                $clean = substr($path, $pos);
                DB::table('product_images')
                    ->where('id', $row->id)
                    ->update(['image_path' => $clean]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No automatic rollback; you would need to restore from backup if required.
    }
};
?>
