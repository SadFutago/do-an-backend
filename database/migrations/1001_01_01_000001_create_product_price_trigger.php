<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tạo hàm tính giá sản phẩm
        DB::unprepared('
            CREATE OR REPLACE FUNCTION calculate_product_price(orig_price NUMERIC, promo_id BIGINT)
            RETURNS NUMERIC AS $$
            DECLARE
                discount_pct DECIMAL(10,2);
                max_discount DECIMAL(15,2);
                discount_amount DECIMAL(15,2);
                is_active SMALLINT;
                new_price DECIMAL(15,2);
            BEGIN
                new_price := orig_price;
                IF promo_id IS NOT NULL THEN
                    SELECT discount_percent, max_discount_amount, status
                    INTO discount_pct, max_discount, is_active
                    FROM promotions
                    WHERE id = promo_id AND deleted_at IS NULL
                    LIMIT 1;
                    IF FOUND AND is_active = 1 THEN
                        discount_amount := orig_price * (discount_pct / 100);
                        IF max_discount IS NOT NULL AND discount_amount > max_discount THEN
                            discount_amount := max_discount;
                        END IF;
                        new_price := GREATEST(0, orig_price - discount_amount);
                    END IF;
                END IF;
                RETURN new_price;
            END;
            $$ LANGUAGE plpgsql;
        ');

        // Tạo trigger cho bảng products
        DB::unprepared('
            CREATE OR REPLACE FUNCTION update_product_price()
            RETURNS TRIGGER AS $$
            BEGIN
                IF TG_OP = \'INSERT\' OR 
                    (TG_OP = \'UPDATE\' AND (NEW.original_price != OLD.original_price OR COALESCE(NEW.promotion_id, 0) != COALESCE(OLD.promotion_id, 0))) THEN
                    NEW.price := calculate_product_price(NEW.original_price, NEW.promotion_id);
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER trigger_update_product_price 
            BEFORE INSERT OR UPDATE ON products
            FOR EACH ROW
            EXECUTE FUNCTION update_product_price();
        ');

        // Tạo trigger cho bảng promotions
        DB::unprepared('
            CREATE OR REPLACE FUNCTION update_product_price_from_promo()
            RETURNS TRIGGER AS $$
            BEGIN
                IF (NEW.deleted_at IS DISTINCT FROM OLD.deleted_at) OR
                    (NEW.discount_percent != OLD.discount_percent) OR
                    (NEW.max_discount_amount IS DISTINCT FROM OLD.max_discount_amount) OR
                    ((NEW.status = 1) != (OLD.status = 1))
                THEN
                    UPDATE products
                    SET price = CASE 
                        WHEN NEW.status != 1 OR NEW.deleted_at IS NOT NULL THEN original_price
                        ELSE calculate_product_price(original_price, NEW.id)
                    END
                    WHERE promotion_id = NEW.id;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER trigger_update_product_price_from_promo
            AFTER UPDATE ON promotions
            FOR EACH ROW
            EXECUTE FUNCTION update_product_price_from_promo();
        ');
    }
    
    public function down(): void
    {
        DB::unprepared('
            DROP TRIGGER IF EXISTS trigger_update_product_price ON products;
            DROP FUNCTION IF EXISTS update_product_price();
            DROP FUNCTION IF EXISTS calculate_product_price(orig_price NUMERIC, promo_id BIGINT);
            DROP TRIGGER IF EXISTS trigger_update_product_price_from_promo ON promotions;
            DROP FUNCTION IF EXISTS update_product_price_from_promo();
        ');
    }
};