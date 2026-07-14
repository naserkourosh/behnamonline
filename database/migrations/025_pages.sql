-- CMS pages (مدیریت صفحات): admin-editable static pages served at /page/{slug}.
-- Pages flagged show_in_footer are appended to the footer quick links.

CREATE TABLE IF NOT EXISTS pages (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(190) NOT NULL,
    slug            VARCHAR(190) NOT NULL UNIQUE,
    body            MEDIUMTEXT NULL,
    seo_title       VARCHAR(190) NULL,
    seo_description VARCHAR(300) NULL,
    show_in_footer  TINYINT(1) NOT NULL DEFAULT 0,
    sort            INT NOT NULL DEFAULT 0,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      DATETIME NOT NULL,
    updated_at      DATETIME NULL,
    KEY idx_pages_active (is_active, show_in_footer, sort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

INSERT INTO pages (title, slug, body, show_in_footer, sort, is_active, created_at) VALUES
('قوانین و مقررات', 'terms',
 '<h2>قوانین استفاده از فروشگاه</h2><p>استفاده از این فروشگاه به معنای پذیرش قوانین جمهوری اسلامی ایران و قوانین تجارت الکترونیک است. ثبت سفارش به منزله قبول شرایط فروش، زمان‌بندی ارسال و ضوابط بازگشت کالا است.</p><h3>ثبت سفارش</h3><p>پس از ثبت سفارش، پیامک تایید برای شما ارسال می‌شود. در صورت ناموجود شدن کالا، مبلغ پرداختی به‌طور کامل بازگردانده می‌شود.</p><h3>بازگشت کالا</h3><p>تا ۷ روز پس از دریافت، در صورت باز نشدن پلمب محصولات آرایشی و بهداشتی، امکان بازگشت وجود دارد.</p>',
 1, 1, 1, NOW()),
('حریم خصوصی', 'privacy',
 '<h2>حریم خصوصی کاربران</h2><p>اطلاعات شخصی شما (نام، شماره تماس و آدرس) صرفاً برای پردازش و ارسال سفارش استفاده می‌شود و در اختیار هیچ شخص یا شرکت ثالثی قرار نمی‌گیرد.</p><p>شماره موبایل شما تنها برای اطلاع‌رسانی وضعیت سفارش و ورود امن به حساب کاربری استفاده می‌شود.</p>',
 1, 2, 1, NOW()),
('راهنمای خرید', 'shopping-guide',
 '<h2>راهنمای ثبت سفارش</h2><ol><li>محصول موردنظر را انتخاب و به سبد خرید اضافه کنید.</li><li>در صفحه سبد خرید، کد تخفیف (در صورت داشتن) را وارد کنید.</li><li>در مرحله تسویه، مشخصات و آدرس دقیق را وارد کنید.</li><li>روش ارسال و پرداخت را انتخاب و سفارش را نهایی کنید.</li></ol><p>پس از پرداخت موفق، کد پیگیری سفارش پیامک می‌شود و از بخش «پیگیری سفارش» قابل مشاهده است.</p>',
 1, 3, 1, NOW());
