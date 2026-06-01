# FamilyTree PHP App

This is a minimal PHP application built without frameworks. It provides:

- Login by phone number + 6-digit PIN
- Signup request workflow with admin approval
- Admin dashboard for pending requests, user search, edit, and delete
- MySQL persistence for users, signup requests, and login PINs
- Email alerts through Amazon SES SMTP
- SMS alerts through Twilio
- Protected `/restricted` area validated through Apache rewrite and PHP sessions

## Files Included

- `login.html` — login page using the existing page style
- `signup.html` — signup page with email, name, and phone fields
- `admin.php` — admin dashboard page
- `logout.php` — clears the session and returns to login
- `restricted/index.php` — protected landing page for signed-in users
- `api/auth/signup.php` — signup request API
- `api/auth/login.php` — login request and PIN send API
- `api/auth/verify_pin.php` — PIN verification and session authentication API
- `api/admin/pending.php` — admin endpoint for pending signup requests
- `api/admin/users.php` — admin endpoint for active users search
- `api/admin/action.php` — admin approve/decline/edit/delete actions
- `config.php` — configuration placeholders for DB, SES, and Twilio
- `functions.php` — shared helper functions
- `restricted/.htaccess` — folder protection routing for the restricted area
- `sql/admin.sql` — full schema and initial admin account
- `sql/users.sql` — general user schema with no seeded rows

## Setup

1. Place the app in your Apache/PHP web root.
2. Create the MySQL database and tables:
   - Run `mysql -u root -p < sql/admin.sql`
   - Optionally, use `sql/users.sql` when importing general users.
3. Update `config.php` with your database credentials.
4. Update `config.php` with SES SMTP credentials and Twilio credentials.

## Important Configuration

Open `config.php` and set the following values:

- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
- `ADMIN_EMAIL` — admin personal email to receive signup notifications
- `SES_SMTP_HOST`, `SES_SMTP_PORT`, `SES_SMTP_USERNAME`, `SES_SMTP_PASSWORD`, `SES_FROM_EMAIL`
- `TWILIO_ACCOUNT_SID`, `TWILIO_AUTH_TOKEN`, `TWILIO_FROM_NUMBER`
- `ADMIN_PHONE` — the phone number for the admin login, e.g. `+1(555)345-6789`

## Signup Flow

1. User fills first name, last name, email, and phone.
2. The app stores a pending signup request.
3. An email is sent to the admin via Amazon SES.
4. Admin approves or declines on `admin.php`.
5. User receives a Twilio SMS with the result.

## Login Flow

1. User enters his phone number on `login.html`.
2. If the number is registered and active, a 6-digit PIN is sent to the phone.
3. User enters the PIN to get authenticated.
4. Regular users are redirected to `/restricted/index.php`.
5. The configured admin phone user is redirected to `/admin.php`.

## Admin Dashboard

- Approve or decline signup requests.
- Search active users.
- Edit user name, email, and phone.
- Delete users with confirmation and SMS notification.

## Notes

- The `restricted` folder is protected by `.htaccess` routing and session checks.
- The app uses raw PHP and can be extended later as needed.
- The default login/signup pages use `background.png`, `logo.png`, and `dashboard.png`.
