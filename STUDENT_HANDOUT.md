Activity: Design and Implementation of Secure Email Authentication (PHP & MySQL)

Objectives
- Design secure email-based registration and verification.
- Implement password hashing, token-based verification, and secure sessions.

Implementation checklist (what you'll complete)
- DB: users table with hashed `password`, `verification_code` (sha256), `token_expiry`, `is_verified`.
- Registration: validate input, hash password with `password_hash()`, generate `bin2hex(random_bytes(32))`, store token hash, send email with raw token.
- Verification: endpoint reads `token`, hashes it, matches DB, checks expiry, sets `is_verified=1`.
- Login: verify password with `password_verify()`, check `is_verified`, regenerate session id, set secure session cookie params.
- Extras: CSRF tokens, rate-limiting, secure cookies, session fingerprinting.

Student exercises

Task A — Design (15 pts)
- Draw a sequence diagram for: register -> send email -> verify -> login.

Task B — Implement registration (25 pts)
- Complete `register` to: validate inputs, hash password, generate and store token hash and expiry, send verification email using PHPMailer and env vars.

Task C — Implement verification (20 pts)
- Implement endpoint to accept `token`, validate hash and expiry, mark account verified and clear token.

Task D — Implement secure login (20 pts)
- Allow login only for verified users, verify password, `session_regenerate_id(true)`, store minimal session data, set secure cookie params.

Task E — Hardening (20 pts)
- Add CSRF protection to forms, add rate-limiting (simple counter + lockout), sanitize inputs and escape outputs.

Assessment rubric (how tasks are graded)
- Password hashing & verification: 20
- Token generation/storage and expiry: 15
- Email sending & link correctness: 15
- Verified-only login enforcement: 10
- Session protection & cookie settings: 15
- Prepared statements & input sanitation: 15
- Extra security (CSRF, rate-limiting): 10
Total: 100

Deliverables
- Working web app with register, verify, login, and logout.
- Short report (1-2 pages) describing security decisions.

