<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE FUNCTION update_product_quantity()
            RETURNS TRIGGER AS $$
            BEGIN
                UPDATE products SET quantity = (
                    SELECT COALESCE(SUM(quantity), 0) FROM inventories
                    WHERE product_id = (
                        CASE
                            WHEN TG_OP = 'DELETE' THEN OLD.product_id
                            ELSE NEW.product_id
                        END
                    )
                    AND deleted_at IS NULL
                )
                WHERE id = (
                    CASE
                        WHEN TG_OP = 'DELETE' THEN OLD.product_id
                        ELSE NEW.product_id
                    END
                );
                RETURN NULL;
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::statement("
            CREATE TRIGGER trigger_update_product_quantity
            AFTER INSERT OR UPDATE OR DELETE ON inventories
            FOR EACH ROW
            EXECUTE FUNCTION update_product_quantity();
        ");
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS trigger_update_product_quantity ON inventories');
        DB::statement('DROP FUNCTION IF EXISTS update_product_quantity()');
    }
};
