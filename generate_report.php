<?php

declare(strict_types=1);

/**
 * Generates Village Link HND project report as a Word (.docx) file.
 * Run: php generate_report.php
 */

final class VillageLinkReportBuilder
{
    private string $assetDir;

    private string $outPath;

    /** @var list<string> */
    private array $parts = [];

    /** @var list<array{rid:string, path:string}> */
    private array $images = [];

    private int $imageCounter = 0;

    /** @var list<array{0:string,1:string,2:string}> */
    private const FIGURES = [
        ['Figure 3.1: System Architecture of the Proposed System', 'diagram_architecture.png', '11'],
        ['Figure 3.2: Use Case Diagram of the Proposed System', 'diagram_use_case.png', '12'],
        ['Figure 3.3: Database Entity Relationship Diagram', 'diagram_erd.png', '13'],
        ['Figure 3.4: Parcel Booking and Delivery Workflow', 'diagram_workflow.png', '15'],
        ['Figure 4.1: Home Page of Village Link', '01_home_page_bw.png', '20'],
        ['Figure 4.2: Customer Dashboard', '02_customer_dashboard_bw.png', '21'],
        ['Figure 4.3: Parcel Booking Interface', '03_book_parcel_bw.png', '22'],
        ['Figure 4.4: Customer Tracking Page with Route Map', '04_tracking_page_bw.png', '23'],
        ['Figure 4.5: Admin Dashboard', '05_admin_dashboard_bw.png', '24'],
        ['Figure 4.6: Admin Parcel Management Page', '06_admin_parcels_bw.png', '25'],
        ['Figure 4.7: Driver Dashboard', '07_driver_dashboard_bw.png', '26'],
        ['Figure 4.8: Driver Delivery Status Update Page', '08_driver_status_update_bw.png', '27'],
    ];

    public function __construct(?string $assetDir = null, ?string $outPath = null)
    {
        $root = __DIR__;
        $candidates = array_filter([
            $assetDir,
            $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'report_assets',
            'C:\\Users\\user\\Documents\\VillageLink_Report_Assets',
        ]);

        foreach ($candidates as $candidate) {
            if ($candidate !== null && is_dir($candidate)) {
                $this->assetDir = $candidate;
                break;
            }
        }

        $this->assetDir ??= $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'report_assets';

        $this->outPath = $outPath ?? $root . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . 'VillageLink_Final_Report.docx';
    }

    public function build(): string
    {
        $this->parts = [];
        $this->images = [];
        $this->imageCounter = 0;

        $this->prePages();
        $this->body();

        return $this->wrapDocument(implode('', $this->parts));
    }

    public function save(?string $path = null): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('PHP ZipArchive extension is required.');
        }

        $path ??= $this->outPath;
        $dir = dirname($path);
        if (! is_dir($dir) && ! mkdir($dir, 0777, true) && ! is_dir($dir)) {
            throw new RuntimeException("Unable to create directory: {$dir}");
        }

        $documentXml = $this->build();
        $rels = [];
        $contentTypes = [
            '/word/document.xml' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml',
            '/word/styles.xml' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml',
            '/word/settings.xml' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.settings+xml',
            '/word/fontTable.xml' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.fontTable+xml',
            '/word/theme/theme1.xml' => 'application/vnd.openxmlformats-officedocument.theme+xml',
            '/docProps/core.xml' => 'application/vnd.openxmlformats-officedocument.core-properties+xml',
            '/docProps/app.xml' => 'application/vnd.openxmlformats-officedocument.extended-properties+xml',
        ];

        foreach ($this->images as $image) {
            $contentTypes['/word/media/' . $image['rid']] = $this->mimeFor($image['path']);
            $rels[] = $image;
        }

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Unable to create file: {$path}");
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml($contentTypes));
        $zip->addFromString('_rels/.rels', $this->rootRelsXml());
        $zip->addFromString('word/document.xml', $documentXml);
        $zip->addFromString('word/_rels/document.xml.rels', $this->documentRelsXml($rels));
        $zip->addFromString('word/styles.xml', $this->stylesXml());
        $zip->addFromString('word/settings.xml', $this->settingsXml());
        $zip->addFromString('word/fontTable.xml', $this->fontTableXml());
        $zip->addFromString('word/theme/theme1.xml', $this->themeXml());
        $zip->addFromString('docProps/core.xml', $this->coreXml());
        $zip->addFromString('docProps/app.xml', $this->appXml());

        foreach ($this->images as $image) {
            $zip->addFile($image['path'], 'word/media/' . $image['rid']);
        }

        $zip->close();

        $documentsCopy = 'C:\\Users\\user\\Documents\\VillageLink_Final_Report.docx';
        if ($path !== $documentsCopy && is_dir(dirname($documentsCopy))) {
            @copy($path, $documentsCopy);
        }

        return $path;
    }

    private function prePages(): void
    {
        $this->center('AI-POWERED SMART COURIER AND PARCEL TRACKING SYSTEM', 36, true, 1600, 200);
        $this->center('VILLAGE LINK', 36, true, 0, 960);
        $this->center('By', 24, false, 0, 400);
        $this->center('Name of Student: ....................................................', 24);
        $this->center('Registration No: ....................................................', 24, false, 0, 760);
        $this->center('A report submitted in partial fulfillment of the requirements for the Higher National Diploma in Information Technology', 24, false, 0, 600);
        $this->center('Name of the Supervisor: ....................................................', 24, false, 0, 560);
        $this->center('Department of Information Technology', 24, true);
        $this->center('Advanced Technological Institute - Kandy', 24);
        $this->center('Sri Lanka Institute of Advanced Technological Education', 24, false, 0, 840);
        $this->center('2026', 24, true);
        $this->pageBreak();

        $this->center('Declaration', 36, true, 0, 600);
        $this->para('I declare that this project report is my own work and has not been submitted in any form for another diploma, degree, or academic award at any institution. Information derived from published and unpublished work of others has been acknowledged in the text and the list of references is given in Chapter 7.');
        $this->para('Name of Student: ............................................................        Signature: ............................................................', 24, false, 'both', 0, 400);
        $this->para('Date: ............................................................', 24, false, 'both', 0, 680);
        $this->para('Supervised by:', 24, true);
        $this->para('Name of Supervisor(s): ..................................................        Signature: ..................................................', 24, false, 'both', 0, 400);
        $this->para('Date: ............................................................');
        $this->pageBreak();

        $this->center('Dedication', 36, true, 0, 600);
        $this->para('This project is dedicated to my parents, teachers, friends, and all the people who encouraged me throughout the development of the Village Link courier and parcel tracking system.');
        $this->pageBreak();

        $this->center('Acknowledgements', 36, true, 0, 600);
        $this->para('I would like to express my sincere gratitude to the Department of Information Technology, Advanced Technological Institute - Kandy, for providing the opportunity to complete this project. I also thank my supervisor for guidance, feedback, and encouragement during the project period.');
        $this->para('I am thankful to my family and friends for their continuous support. I also acknowledge the open-source communities and documentation resources that supported the development of this Laravel-based web application.');
        $this->pageBreak();

        $this->center('Abstract', 36, true, 0, 480);
        foreach ([
            'Village Link is an AI-powered smart courier and parcel tracking system developed to improve parcel booking, delivery assignment, payment handling, customer communication, and real-time tracking. The project addresses unclear delivery status, manual driver assignment, delayed updates, weak complaint handling, and the lack of a single platform for end-to-end courier management.',
            'The system provides separate workspaces for customers, drivers, and administrators. Customers can register, book parcels, calculate delivery cost using weight and parcel dimensions, make payments, download invoices, track delivery status, request refunds, submit complaints, and use an AI assistant. Drivers can view assigned deliveries, update delivery status, share GPS location, upload delivery proof, and maintain vehicle details. Administrators can manage parcels, payments, drivers, users, complaints, refunds, reports, and driver assignment.',
            'The system was implemented using PHP, Laravel, Blade templates, Tailwind CSS, Alpine.js, SQLite/MySQL-ready database structure, SMTP email, map services, and automated tests. The final output is a professional web-based courier system that supports booking, payment, delivery, tracking, reporting, and customer support workflows in one integrated application.',
        ] as $text) {
            $this->para($text);
        }
        $this->pageBreak();

        $this->center('Table of Contents', 36, true, 0, 360);
        $this->para('Page', 24, true, 'right', 0, 80);
        foreach ($this->tocItems() as [$text, $level, $page]) {
            $this->contentsLine($text, $level, $page);
        }
        $this->pageBreak();

        $this->center('List of Figures', 36, true, 0, 360);
        $this->para('Page', 24, true, 'right', 0, 80);
        foreach (self::FIGURES as [$cap, , $page]) {
            $this->contentsLine($cap, 0, $page);
        }
        $this->pageBreak();

        $this->center('List of Tables', 36, true, 0, 360);
        $this->para('Page', 24, true, 'right', 0, 80);
        foreach ($this->tableList() as [$cap, $page]) {
            $this->contentsLine($cap, 0, $page);
        }
        $this->pageBreak();
    }

    private function body(): void
    {
        $this->chapter(1, 'Introduction');
        $this->heading('1.1 Introduction');
        $this->para('This chapter introduces the Village Link project, the problem area, the project objectives, the project scope, and the users who interact with the system. Courier and parcel delivery services require timely booking, secure payment, accurate driver assignment, and visible delivery updates.');
        $this->heading('1.2 Project Overview');
        $this->para('Village Link is an AI-powered smart courier and parcel tracking system designed for customers, drivers, and administrators. The system supports parcel booking, live route tracking, payment handling, driver assignment, delivery proof, customer complaints, refund requests, loyalty points, multilingual interfaces, dark/light theme selection, and AI assistant support.');
        $this->para('The system was implemented as a Laravel web application. It uses role-based dashboards so that every user sees only the functions relevant to their role.');
        $this->heading('1.3 Problem Statement');
        $this->para('Traditional small courier services often depend on phone calls, manual logs, and informal driver communication. This creates delayed status updates, limited tracking visibility, unclear delivery responsibility, payment confirmation delays, and weak complaint handling.');
        $this->para('Village Link solves these issues by providing a single web system for the full delivery lifecycle. The system prevents assigning busy drivers to another active delivery, records delivery status history, supports GPS-based map updates, sends password reset email links, and allows administrators to monitor payments, refunds, complaints, and reports.');
        $this->heading('1.4 Objectives');
        $this->table(['No.', 'Objective', 'Description'], [
            ['1', 'Provide online parcel booking', 'Customers can enter sender, receiver, parcel, pickup, and drop-off details.'],
            ['2', 'Automate price and ETA calculation', 'System calculates delivery cost using weight, parcel size, distance, and delivery speed.'],
            ['3', 'Support real-time parcel tracking', 'Customers can view status history, route map, ETA, and live driver location.'],
            ['4', 'Improve driver assignment', 'Admin can assign only available drivers and avoid overlapping active delivery jobs.'],
            ['5', 'Provide secure accounts and password reset', 'Users can register, login, change password, and reset forgotten passwords through email links.'],
            ['6', 'Support operations reporting', 'Admin can view parcel, revenue, payment, driver, complaint, and refund information.'],
        ], 'Table 1.1: Project Objectives');
        $this->heading('1.5 Scope of the Project');
        $this->para('The scope includes public pages, registration and login, customer booking, parcel management, payments, invoices, tracking, driver delivery updates, administrator functions, AI assistant, complaint handling, refund/cancel requests, loyalty points, and report dashboards.');
        $this->heading('1.6 Summary');
        $this->para('This chapter introduced the system and project objectives. The next chapter explains the tools and technologies used.');

        $this->chapter(2, 'Tools and Technologies Used');
        $this->heading('2.1 Introduction');
        $this->para('This chapter describes the software tools, frameworks, libraries, and services used for Village Link. Laravel was selected because it provides routing, MVC structure, authentication, database migrations, validation, mail handling, and testing support [3].');
        $this->heading('2.2 Software and Tools');
        $this->table(['Tool / Technology', 'Use in the Project'], [
            ['PHP 8.2', 'Server-side language used to run the Laravel application [7].'],
            ['Laravel 12', 'Main MVC framework for routes, controllers, models, middleware, validation, mail, and testing [3].'],
            ['Laravel Breeze', 'Authentication scaffolding for login, registration, password reset, and profile features.'],
            ['Blade Templates', 'Laravel view layer used to build reusable interface pages.'],
            ['Tailwind CSS', 'Utility-first CSS framework for responsive interface styling [9].'],
            ['Alpine.js', 'Lightweight JavaScript for booking forms, GPS buttons, and UI behavior [1].'],
            ['SQLite / MySQL-ready DB', 'SQLite is used locally while migrations support MySQL deployment [8].'],
            ['Leaflet, OSM, OSRM', 'Map and road route support for parcel tracking [5].'],
            ['SMTP Email', 'Password reset links, complaint replies, and notification emails.'],
            ['OpenAI API', 'Optional live AI assistant provider for role-aware support [4].'],
            ['PayHere-ready gateway', 'Payment gateway configuration prepared for Sri Lankan workflow [6].'],
            ['Pest Testing', 'Automated feature tests for system workflows.'],
        ], 'Table 2.1: Tools and Technologies Used');
        $this->heading('2.3 Justification of Tools');
        $this->para('Laravel, Blade, Tailwind CSS, and Alpine.js were chosen because they provide a practical balance between backend security, responsive design, and lightweight interactivity. SQLite made development simple, while the migration structure keeps the system ready for MySQL deployment. Map and route services were selected to make parcel tracking more realistic.');
        $this->heading('2.4 Summary');
        $this->para('This chapter explained the technical stack. The next chapter presents the project design and development process.');

        $this->chapter(3, 'Project Design and Development Process');
        $this->heading('3.1 Introduction');
        $this->para('This chapter explains how Village Link was planned, designed, and developed. It includes requirement analysis, system architecture, use cases, database design, development stages, and challenges.');
        $this->heading('3.2 Planning and Requirement Analysis');
        $this->para('The planning stage identified three main users: customer, driver, and administrator. Customers need booking, tracking, payment, invoice, complaint, refund, and account recovery. Drivers need assigned deliveries, GPS, status updates, and proof upload. Administrators need parcel, payment, driver, user, complaint, refund, and report management.');
        $this->para('A key requirement was that a driver must not receive another active parcel while already completing a delivery. The system includes busy-driver validation and shows only available drivers during assignment.');
        $this->heading('3.3 System Architecture');
        $this->para('Village Link follows an MVC architecture. Browser requests pass through Laravel routes and controllers. Controllers validate input and call service classes such as pricing, geocoding, AI assistant, loyalty, and payment services. Eloquent models communicate with the database. Figure 3.1 shows the top-level architecture.');
        $this->figure('diagram_architecture.png', 'Figure 3.1: System Architecture of the Proposed System');
        $this->para('The use cases are divided according to the user roles. Customers handle booking and tracking, drivers handle delivery updates, and administrators manage operations. Figure 3.2 shows the use case diagram.');
        $this->figure('diagram_use_case.png', 'Figure 3.2: Use Case Diagram of the Proposed System');
        $this->heading('3.4 Database Design');
        $this->para('The database was designed around users, parcels, payments, parcel statuses, parcel locations, driver profiles, complaints, refund requests, loyalty transactions, ratings, and notifications. Figure 3.3 shows the entity relationship diagram.');
        $this->figure('diagram_erd.png', 'Figure 3.3: Database Entity Relationship Diagram');
        $this->table(['Entity', 'Purpose'], [
            ['users', 'Stores customer, driver, and admin account data.'],
            ['driver_profiles', 'Stores vehicle type, vehicle number, licence, shift, availability, and GPS status.'],
            ['parcels', 'Stores sender, receiver, pickup, delivery, price, payment, driver, and status details.'],
            ['payments', 'Stores payment method, reference, amount, status, and loyalty discount information.'],
            ['parcel_statuses', 'Stores status history and notes for each parcel.'],
            ['parcel_locations', 'Stores live GPS updates sent by drivers.'],
            ['complaints', 'Stores customer complaint records and admin status.'],
            ['refund_requests', 'Stores customer cancel/refund requests and admin approval information.'],
        ], 'Table 3.1: Main Database Entities');
        $this->heading('3.5 Development Process');
        $this->para('Development was completed in stages: authentication, customer booking, pricing, payment, invoice, driver assignment, driver status update, live tracking, admin dashboards, complaints, refunds, loyalty points, multilingual text, theme switch, AI assistant, SMTP reset emails, and automated tests.');
        $this->figure('diagram_workflow.png', 'Figure 3.4: Parcel Booking and Delivery Workflow');
        $this->heading('3.6 Challenges Faced');
        $this->para('Challenges included generating realistic map routes, handling browser GPS permission, making reset password links open from another device, and preventing multiple active driver assignments. These were solved through map service fallbacks, route GPS mode, separate password reset URL, and driver availability validation.');
        $this->heading('3.7 Summary');
        $this->para('This chapter described planning, architecture, database design, development stages, and challenges.');

        $this->chapter(4, 'Key Features and Functions');
        $this->heading('4.1 Introduction');
        $this->para('This chapter explains the main features and includes screenshots of the implemented system.');
        $this->heading('4.2 Feature Summary');
        $this->table(['Feature', 'Description'], [
            ['Public Pages', 'Home, services, about us, and contact pages with courier branding.'],
            ['Authentication', 'Registration, login, forgot password, reset password, change password, and profile management.'],
            ['Customer Dashboard', 'Parcel statistics, recent parcels, booking shortcuts, tracking, payments, refunds, complaints, and AI assistant.'],
            ['Parcel Booking', 'Sender and receiver details, locations, parcel type, delivery speed, size calculator, price, and ETA preview.'],
            ['Payment and Invoice', 'Manual payment, PayHere-ready gateway, invoice page, PDF download, and payment status.'],
            ['Live Tracking', 'Status timeline, route map, pickup/drop markers, driver details, vehicle marker, GPS updates, and ETA.'],
            ['Driver Workspace', 'Assigned deliveries, active job, GPS update, status change, notes, and proof upload.'],
            ['Admin Workspace', 'Dashboard, parcels, driver assignment, payments, refunds, users, drivers, complaints, contact messages, and reports.'],
            ['AI Assistant', 'Role-aware support for parcel, payment, complaint, driver, and setup questions.'],
            ['Extra Features', 'Dark/light mode, English/Tamil/Sinhala switch, loyalty points, refunds, and driver performance fields.'],
        ], 'Table 4.1: Main Features of Village Link');
        $this->heading('4.3 Screenshots and Illustrations');
        $descriptions = [
            'The home page presents the Village Link identity and provides entry points for registration, login, booking, and tracking.',
            'The customer dashboard provides parcel summary and quick access to customer functions.',
            'The booking page collects sender, receiver, route, parcel, weight, and dimension details.',
            'The tracking page displays status history, live route, ETA, driver details, and parcel details.',
            'The admin dashboard provides parcel, revenue, pending job, chart, and report information.',
            'The admin parcel page supports parcel review and driver assignment.',
            'The driver dashboard shows assigned jobs, active delivery details, and driver profile status.',
            'The driver status page supports status update, GPS, route GPS, notes, and proof upload.',
        ];
        foreach (array_slice(self::FIGURES, 4) as $index => [$cap, $file, $_page]) {
            $label = explode(':', $cap, 2)[0];
            $this->para($descriptions[$index] . ' ' . $label . ' shows this interface.');
            $this->figure($file, $cap);
        }
        $this->heading('4.4 Summary');
        $this->para('This chapter explained the main functions with screenshots.');

        $this->chapter(5, 'Results and Testing');
        $this->heading('5.1 Introduction');
        $this->para('This chapter presents the final outcome and testing. Testing was completed using manual browser testing and automated Laravel feature tests.');
        $this->heading('5.2 Final Outcome');
        $this->para('The final output is a working courier and parcel tracking system with role-based dashboards, booking, driver assignment, payments, invoices, live route tracking, delivery status history, proof upload, complaints, refunds, loyalty points, AI assistant support, and real email password reset setup.');
        $this->heading('5.3 Testing and Feedback');
        $this->para('Automated tests verified authentication, password reset, parcel booking, GPS updates, driver assignment, payments, refunds, complaints, loyalty points, AI assistant responses, and route preview. The final automated suite passed 44 tests with 155 assertions.');
        $this->table(['Test ID', 'Test Area', 'Expected Result', 'Status'], [
            ['TC01', 'Customer registration and login', 'Valid user can register and login successfully.', 'Pass'],
            ['TC02', 'Forgot password email reset', 'Reset link is sent and opens the reset form.', 'Pass'],
            ['TC03', 'Parcel booking', 'Customer can book a parcel and receive a tracking ID.', 'Pass'],
            ['TC04', 'Price and volumetric weight', 'System calculates chargeable weight and price.', 'Pass'],
            ['TC05', 'Payment and invoice', 'Customer can submit payment and view invoice.', 'Pass'],
            ['TC06', 'Admin driver assignment', 'Admin cannot assign a busy driver to another active parcel.', 'Pass'],
            ['TC07', 'Driver status update', 'Driver can update status and GPS location.', 'Pass'],
            ['TC08', 'Customer tracking', 'Customer can view route, marker, status history, and ETA.', 'Pass'],
            ['TC09', 'Refund request', 'Customer can submit refund/cancel request and admin can approve/reject it.', 'Pass'],
            ['TC10', 'AI assistant', 'Assistant answers role-aware parcel and system questions.', 'Pass'],
        ], 'Table 5.1: Functional Test Cases');
        $this->para('Manual testing confirmed that dashboards update after booking and assignment. Browser testing confirmed that the customer can track a parcel and that the driver can start live GPS or route GPS fallback.');
        $this->heading('5.4 Summary');
        $this->para('This chapter described the final outcome and testing results.');

        $this->chapter(6, 'Conclusion');
        $this->heading('6.1 Introduction');
        $this->para('This chapter concludes the report by summarizing the achievements and future improvements.');
        $this->heading('6.2 Summary');
        $this->para('Village Link demonstrates a smart courier and parcel tracking system that supports the delivery workflow from booking to final delivery. It includes customer booking, payment, invoice, tracking, complaints, refunds, loyalty, AI assistant, driver delivery management, and administrator control.');
        $this->para('The project achieved its main objective by creating a professional role-based courier system with password reset email, driver availability validation, GPS tracking, route display, delivery proof, and operational dashboards.');
        $this->heading('6.3 Future Improvements');
        $this->para('Future improvements include a dedicated mobile app for drivers, push notifications, SMS alerts, QR code scanning, barcode labels, warehouse hub management, customer address book suggestions, automated route optimization, and production payment gateway integration.');

        $this->chapter(7, 'References');
        foreach ([
            '[1] Alpine.js. (2026). Alpine.js documentation. Available: https://alpinejs.dev/',
            '[2] Barry vd. Heuvel. (2026). Laravel DOMPDF documentation. Available: https://github.com/barryvdh/laravel-dompdf',
            '[3] Laravel. (2026). Laravel documentation. Available: https://laravel.com/docs',
            '[4] OpenAI. (2026). OpenAI API documentation. Available: https://platform.openai.com/docs',
            '[5] OpenStreetMap Foundation. (2026). OpenStreetMap documentation and map data. Available: https://www.openstreetmap.org/',
            '[6] PayHere. (2026). PayHere payment gateway documentation. Available: https://www.payhere.lk/',
            '[7] PHP Group. (2026). PHP manual. Available: https://www.php.net/manual/en/',
            '[8] SQLite Consortium. (2026). SQLite documentation. Available: https://www.sqlite.org/docs.html',
            '[9] Tailwind Labs. (2026). Tailwind CSS documentation. Available: https://tailwindcss.com/docs',
            '[10] Vite. (2026). Vite documentation. Available: https://vite.dev/guide/',
        ] as $ref) {
            $this->para($ref, 24, false, 'both', 0, 80);
        }

        $this->pageBreak();
        $this->center('Appendix A - Individual Contribution to the Project', 36, true, 0, 360);
        $this->para('This appendix describes the individual contribution made to Village Link. The contribution included selecting the project idea, identifying courier workflow problems, designing customer, driver, and admin modules, preparing the database structure, implementing the Laravel application, creating interfaces, testing the system, and preparing the final documentation.');
        $this->para('Important contributions included parcel booking, real-time tracking, driver assignment, dashboard pages, payment and invoice workflow, refund and complaint handling, AI assistant setup, SMTP password reset, route map improvements, driver GPS fallback, multilingual support, dark/light mode, loyalty points, and automated tests.');

        $this->pageBreak();
        $this->center('Appendix B - Installation and Configuration', 36, true, 0, 360);
        $this->table(['Configuration Item', 'Description'], [
            ['Application URL', 'Local browser: http://127.0.0.1:8000; phone reset link: http://192.168.8.134:8000'],
            ['Database', 'SQLite database used in development; migrations support MySQL deployment.'],
            ['Main commands', 'php artisan migrate, php artisan storage:link, npm run build, php artisan serve --host=0.0.0.0 --port=8000'],
            ['SMTP', 'MAIL_MAILER=smtp, smtp.gmail.com, port 587, Gmail App Password.'],
            ['AI', 'AI_ASSISTANT_PROVIDER=openai with API key for live assistant; local mode works without key.'],
            ['Payment Gateway', 'PayHere sandbox/production keys can be configured in .env.'],
            ['Storage', 'Delivery proof photos and uploads use Laravel public storage link.'],
        ], 'Table B.1: Installation and Configuration Summary');

        $this->pageBreak();
        $this->center('Appendix C - User Manual', 36, true, 0, 360);
        $this->heading('Customer Steps');
        foreach ([
            'Register or login.',
            'Open Book Parcel and enter sender, receiver, pickup, drop-off, parcel, weight, and dimension details.',
            'Review price, ETA, and route preview, then submit booking.',
            'Complete payment and download invoice.',
            'Use Track Parcel or My Parcels to view route, status history, ETA, and driver details.',
            'Submit complaint, refund, or rating when required.',
        ] as $step) {
            $this->numbered($step);
        }
        $this->heading('Driver Steps');
        foreach ([
            'Login using the driver account.',
            'Open Driver Dashboard or Deliveries.',
            'Open Update Status for an active parcel.',
            'Start Live GPS or Follow Route GPS and keep the page open.',
            'Update delivery status and upload proof when delivered.',
        ] as $step) {
            $this->numbered($step);
        }
        $this->heading('Admin Steps');
        foreach ([
            'Login using the admin account.',
            'Open Admin Dashboard to view parcels, revenue, pending jobs, and reports.',
            'Open Parcels and assign an available driver.',
            'Manage payments, refunds, complaints, users, drivers, and reports.',
            'Use AI Assistant for system summary and operational questions.',
        ] as $step) {
            $this->numbered($step);
        }

        $this->pageBreak();
        $this->center('Appendix D - Data Dictionary', 36, true, 0, 360);
        $this->table(['Table', 'Important Fields', 'Purpose'], [
            ['users', 'id, name, email, password, role, phone, address, loyalty_points, theme_preference', 'Stores all account types.'],
            ['driver_profiles', 'user_id, vehicle_type, vehicle_number, license_number, availability_status, current_lat, current_lng', 'Stores driver and vehicle information.'],
            ['parcels', 'tracking_id, user_id, agent_id, sender_name, receiver_name, pickup_location, delivery_location, price, status', 'Stores parcel booking and delivery information.'],
            ['payments', 'parcel_id, user_id, amount, method, provider, reference, status, loyalty fields', 'Stores payment and invoice information.'],
            ['parcel_statuses', 'parcel_id, user_id, status, note, created_at', 'Stores chronological status history.'],
            ['parcel_locations', 'parcel_id, driver_id, latitude, longitude, note, created_at', 'Stores live GPS points.'],
            ['complaints', 'user_id, parcel_id, category, subject, message, status', 'Stores support complaints.'],
            ['refund_requests', 'parcel_id, payment_id, type, reason, requested_amount, status', 'Stores refund and cancel requests.'],
            ['ratings', 'parcel_id, user_id, rating, comment', 'Stores customer delivery feedback.'],
        ], 'Table D.1: Data Dictionary of Main Tables');
    }

    /** @return list<array{0:string,1:int,2:string}> */
    private function tocItems(): array
    {
        return [
            ['Declaration', 0, 'i'], ['Dedication', 0, 'ii'], ['Acknowledgements', 0, 'iii'], ['Abstract', 0, 'iv'],
            ['List of Figures', 0, 'vi'], ['List of Tables', 0, 'vii'], ['Chapter 1 - Introduction', 0, '1'],
            ['1.1 Introduction', 1, '1'], ['1.2 Project Overview', 1, '1'], ['1.3 Problem Statement', 1, '2'],
            ['1.4 Objectives', 1, '3'], ['1.5 Scope of the Project', 1, '4'], ['1.6 Summary', 1, '4'],
            ['Chapter 2 - Tools and Technologies Used', 0, '5'], ['2.1 Introduction', 1, '5'], ['2.2 Software and Tools', 1, '6'],
            ['2.3 Justification of Tools', 1, '7'], ['2.4 Summary', 1, '8'],
            ['Chapter 3 - Project Design and Development Process', 0, '9'], ['3.1 Introduction', 1, '9'],
            ['3.2 Planning and Requirement Analysis', 1, '10'], ['3.3 System Architecture', 1, '11'],
            ['3.4 Database Design', 1, '13'], ['3.5 Development Process', 1, '15'], ['3.6 Challenges Faced', 1, '16'], ['3.7 Summary', 1, '16'],
            ['Chapter 4 - Key Features and Functions', 0, '17'], ['4.1 Introduction', 1, '17'], ['4.2 Feature Summary', 1, '18'],
            ['4.3 Screenshots and Illustrations', 1, '20'], ['4.4 Summary', 1, '27'],
            ['Chapter 5 - Results and Testing', 0, '28'], ['5.1 Introduction', 1, '28'], ['5.2 Final Outcome', 1, '29'],
            ['5.3 Testing and Feedback', 1, '30'], ['5.4 Summary', 1, '31'],
            ['Chapter 6 - Conclusion', 0, '32'], ['6.1 Introduction', 1, '32'], ['6.2 Summary', 1, '33'], ['6.3 Future Improvements', 1, '34'],
            ['Chapter 7 - References', 0, '35'],
            ['Appendix A - Individual Contribution to the Project', 0, '37'],
            ['Appendix B - Installation and Configuration', 0, '38'],
            ['Appendix C - User Manual', 0, '40'],
            ['Appendix D - Data Dictionary', 0, '42'],
        ];
    }

    /** @return list<array{0:string,1:string}> */
    private function tableList(): array
    {
        return [
            ['Table 1.1: Project Objectives', '3'],
            ['Table 2.1: Tools and Technologies Used', '6'],
            ['Table 3.1: Main Database Entities', '14'],
            ['Table 4.1: Main Features of Village Link', '18'],
            ['Table 5.1: Functional Test Cases', '30'],
            ['Table B.1: Installation and Configuration Summary', '38'],
            ['Table D.1: Data Dictionary of Main Tables', '42'],
        ];
    }

    private function chapter(int $num, string $title): void
    {
        if ($num > 1) {
            $this->pageBreak();
        }
        $this->center("Chapter {$num}", 36, true, 0, 80);
        $this->center($title, 36, true, 0, 360);
    }

    private function heading(string $text): void
    {
        $this->parts[] = '<w:p><w:pPr><w:spacing w:before="200" w:after="120" w:line="360" w:lineRule="auto"/><w:jc w:val="left"/></w:pPr>'
            . $this->run($text, 24, true) . '</w:p>';
    }

    private function para(string $text, int $size = 24, bool $bold = false, string $align = 'both', int $before = 0, int $after = 120): void
    {
        $this->parts[] = '<w:p><w:pPr><w:spacing w:before="' . $before . '" w:after="' . $after . '" w:line="360" w:lineRule="auto"/>'
            . '<w:jc w:val="' . $this->esc($align) . '"/></w:pPr>' . $this->run($text, $size, $bold) . '</w:p>';
    }

    private function center(string $text, int $size = 24, bool $bold = false, int $before = 0, int $after = 120): void
    {
        $this->para($text, $size, $bold, 'center', $before, $after);
    }

    private function contentsLine(string $text, int $level, string $page): void
    {
        $indent = $level * 360;
        $dots = str_repeat('.', max(3, 70 - strlen($text) - ($level * 4)));
        $this->parts[] = '<w:p><w:pPr><w:ind w:left="' . $indent . '"/><w:spacing w:after="40" w:line="240" w:lineRule="auto"/></w:pPr>'
            . $this->run($text, 22, $level === 0)
            . $this->run(' ' . $dots . ' ' . $page, 22, false)
            . '</w:p>';
    }

    private function numbered(string $text): void
    {
        static $n = 0;
        $n++;
        $this->parts[] = '<w:p><w:pPr><w:ind w:left="360"/>'
            . '<w:spacing w:after="80" w:line="360" w:lineRule="auto"/></w:pPr>'
            . $this->run($n . '. ' . $text) . '</w:p>';
    }

    /** @param list<string> $headers @param list<list<string>> $rows */
    private function table(array $headers, array $rows, string $caption): void
    {
        $xml = '<w:tbl><w:tblPr><w:tblW w:w="5000" w:type="pct"/><w:tblBorders>'
            . '<w:top w:val="single" w:sz="6" w:space="0" w:color="000000"/>'
            . '<w:left w:val="single" w:sz="6" w:space="0" w:color="000000"/>'
            . '<w:bottom w:val="single" w:sz="6" w:space="0" w:color="000000"/>'
            . '<w:right w:val="single" w:sz="6" w:space="0" w:color="000000"/>'
            . '<w:insideH w:val="single" w:sz="6" w:space="0" w:color="000000"/>'
            . '<w:insideV w:val="single" w:sz="6" w:space="0" w:color="000000"/>'
            . '</w:tblBorders></w:tblPr><w:tblGrid>';
        foreach ($headers as $_) {
            $xml .= '<w:gridCol w:w="2000"/>';
        }
        $xml .= '</w:tblGrid><w:tr>';
        foreach ($headers as $header) {
            $xml .= '<w:tc><w:tcPr><w:shd w:val="clear" w:color="auto" w:fill="EDEDED"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr>'
                . $this->run($header, 20, true) . '</w:p></w:tc>';
        }
        $xml .= '</w:tr>';
        foreach ($rows as $row) {
            $xml .= '<w:tr>';
            foreach ($row as $i => $cell) {
                $align = ($i === 0 || $i === count($row) - 1) ? 'center' : 'left';
                $xml .= '<w:tc><w:tcPr/><w:p><w:pPr><w:jc w:val="' . $align . '"/></w:pPr>'
                    . $this->run((string) $cell, 20) . '</w:p></w:tc>';
            }
            $xml .= '</w:tr>';
        }
        $xml .= '</w:tbl>';
        $this->parts[] = $xml;
        $this->para($caption, 20, true, 'center', 80, 200);
    }

    private function figure(string $file, string $caption): void
    {
        $path = $this->assetDir . DIRECTORY_SEPARATOR . $file;
        if (is_file($path)) {
            [$width, $height] = @getimagesize($path) ?: [800, 600];
            $emuPerPixel = 9525;
            $widthEmu = (int) round($width * $emuPerPixel);
            $heightEmu = (int) round($height * $emuPerPixel);
            $maxWidth = 5220000;
            $maxHeight = 3657600;
            $scale = min($maxWidth / $widthEmu, $maxHeight / $heightEmu, 1.0);
            $cx = (int) round($widthEmu * $scale);
            $cy = (int) round($heightEmu * $scale);
            $this->imageCounter++;
            $rid = 'image' . $this->imageCounter . '.' . pathinfo($path, PATHINFO_EXTENSION);
            $relId = 'rId' . (100 + $this->imageCounter);
            $this->images[] = ['rid' => $rid, 'path' => $path, 'rel' => $relId, 'cx' => $cx, 'cy' => $cy];
            $this->parts[] = '<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:drawing>'
                . '<wp:inline distT="0" distB="0" distL="0" distR="0" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing">'
                . '<wp:extent cx="' . $cx . '" cy="' . $cy . '"/><wp:docPr id="' . $this->imageCounter . '" name="' . $this->esc($file) . '"/>'
                . '<a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">'
                . '<a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">'
                . '<pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'
                . '<pic:nvPicPr><pic:cNvPr id="0" name="' . $this->esc($file) . '"/><pic:cNvPicPr/></pic:nvPicPr>'
                . '<pic:blipFill><a:blip r:embed="' . $relId . '" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/>'
                . '<a:stretch><a:fillRect/></a:stretch></pic:blipFill>'
                . '<pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $cx . '" cy="' . $cy . '"/></a:xfrm>'
                . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic>'
                . '</a:graphicData></a:graphic></wp:inline></w:drawing></w:r></w:p>';
        } else {
            $this->para('[Insert image: ' . $file . ']', 20, false, 'center', 80, 80);
        }
        $this->para($caption, 20, true, 'center', 80, 200);
    }

    private function pageBreak(): void
    {
        $this->parts[] = '<w:p><w:r><w:br w:type="page"/></w:r></w:p>';
    }

    private function run(string $text, int $size = 24, bool $bold = false, bool $italic = false): string
    {
        $boldXml = $bold ? '<w:b/>' : '';
        $italicXml = $italic ? '<w:i/>' : '';

        return '<w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/>'
            . '<w:color w:val="000000"/><w:sz w:val="' . $size . '"/>' . $boldXml . $italicXml
            . '</w:rPr><w:t xml:space="preserve">' . $this->esc($text) . '</w:t></w:r>';
    }

    private function wrapDocument(string $body): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" '
            . 'xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" '
            . 'xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" '
            . 'xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'
            . '<w:body>' . $body
            . '<w:sectPr><w:pgSz w:w="11906" w:h="16838"/>'
            . '<w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="2160" w:header="720" w:footer="720" w:gutter="0"/>'
            . '</w:sectPr></w:body></w:document>';
    }

    /** @param array<string,string> $items */
    private function contentTypesXml(array $items): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>';
        foreach ($items as $part => $type) {
            $ext = pathinfo($part, PATHINFO_EXTENSION);
            if ($ext === 'png') {
                continue;
            }
            if ($ext === 'jpeg' || $ext === 'jpg') {
                continue;
            }
            $xml .= '<Override PartName="' . $part . '" ContentType="' . $type . '"/>';
        }
        $xml .= '<Default Extension="png" ContentType="image/png"/>'
            . '<Default Extension="jpeg" ContentType="image/jpeg"/>'
            . '<Default Extension="jpg" ContentType="image/jpeg"/>'
            . '</Types>';

        return $xml;
    }

    private function rootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    /** @param list<array{rid:string,path:string,rel:string,cx:int,cy:int}> $images */
    private function documentRelsXml(array $images): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/settings" Target="settings.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/fontTable" Target="fontTable.xml"/>'
            . '<Relationship Id="rId4" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="theme/theme1.xml"/>';
        foreach ($images as $image) {
            $xml .= '<Relationship Id="' . $image['rel'] . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/' . $image['rid'] . '"/>';
        }
        $xml .= '</Relationships>';

        return $xml;
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/>'
            . '<w:sz w:val="24"/></w:rPr></w:rPrDefault></w:docDefaults>'
            . '</w:styles>';
    }

    private function settingsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:settings xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:zoom w:percent="100"/>'
            . '<w:defaultTabStop w:val="720"/>'
            . '<w:characterSpacingControl w:val="doNotCompress"/>'
            . '</w:settings>';
    }

    private function fontTableXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:fonts xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:font w:name="Times New Roman"><w:panose1 w:val="02020603050405020304"/>'
            . '<w:charset w:val="00"/><w:family w:val="roman"/><w:pitch w:val="variable"/>'
            . '<w:sig w:usb0="00000003" w:usb1="00000000" w:usb2="00000000" w:usb3="00000000" w:csb0="00000001" w:csb1="00000000"/>'
            . '</w:font></w:fonts>';
    }

    private function themeXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<a:theme xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" name="Office Theme">'
            . '<a:themeElements><a:clrScheme name="Office">'
            . '<a:dk1><a:sysClr val="windowText" lastClr="000000"/></a:dk1>'
            . '<a:lt1><a:sysClr val="window" lastClr="FFFFFF"/></a:lt1>'
            . '<a:dk2><a:srgbClr val="44546A"/></a:dk2>'
            . '<a:lt2><a:srgbClr val="E7E6E6"/></a:lt2>'
            . '<a:accent1><a:srgbClr val="4472C4"/></a:accent1>'
            . '<a:accent2><a:srgbClr val="ED7D31"/></a:accent2>'
            . '<a:accent3><a:srgbClr val="A5A5A5"/></a:accent3>'
            . '<a:accent4><a:srgbClr val="FFC000"/></a:accent4>'
            . '<a:accent5><a:srgbClr val="5B9BD5"/></a:accent5>'
            . '<a:accent6><a:srgbClr val="70AD47"/></a:accent6>'
            . '<a:hlink><a:srgbClr val="0563C1"/></a:hlink>'
            . '<a:folHlink><a:srgbClr val="954F72"/></a:folHlink>'
            . '</a:clrScheme></a:themeElements></a:theme>';
    }

    private function coreXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
            . 'xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:title>Village Link Final Report</dc:title>'
            . '<dc:creator>Village Link Project</dc:creator>'
            . '<cp:lastModifiedBy>Village Link Project</cp:lastModifiedBy>'
            . '</cp:coreProperties>';
    }

    private function appXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties">'
            . '<Application>PHP Report Generator</Application></Properties>';
    }

    private function mimeFor(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'image/png',
        };
    }

    private function esc(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}

$builder = new VillageLinkReportBuilder();
$path = $builder->save();
echo "Report created: {$path}\n";
