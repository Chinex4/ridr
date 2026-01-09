## Ride API v1

Base path: `/api/v1`

### Auth
- `POST /register`
- `POST /login`
- `GET /me` (auth:api)
- `POST /logout` (auth:api)
- `POST /logout-all` (auth:api)

### Rider
- `POST /fare/quote` (auth:api, role:rider)
- `POST /rides` (auth:api, role:rider, Idempotency-Key optional)
- `GET /rides` (auth:api)
- `GET /rides/{ride}` (auth:api)
- `POST /rides/{ride}/cancel` (auth:api)
- `POST /rides/{ride}/pay/init` (auth:api, role:rider, Idempotency-Key optional)
- `GET /payments/{reference}` (auth:api)

### Driver
- `POST /driver/apply` (auth:api)
- `GET /driver/profile` (auth:api, role:driver)
- `POST /driver/kyc/submit` (auth:api, role:driver)
- `POST /driver/location` (auth:api, role:driver, kyc.approved)
- `POST /driver/online` (auth:api, role:driver, kyc.approved)
- `POST /driver/offline` (auth:api, role:driver)
- `GET /driver/rides/available` (auth:api, role:driver, kyc.approved)
- `POST /driver/rides/{ride}/accept` (auth:api, role:driver, kyc.approved)
- `POST /driver/rides/{ride}/arrive` (auth:api, role:driver, kyc.approved)
- `POST /driver/rides/{ride}/start` (auth:api, role:driver, kyc.approved)
- `POST /driver/rides/{ride}/complete` (auth:api, role:driver, kyc.approved)

KYC required document types:
- `government_id`
- `driver_license`
- `selfie`
- `proof_of_address`
- `vehicle_registration`
- `insurance`
- `roadworthiness`
- `vehicle_photo_exterior`
- `vehicle_photo_interior`

### Admin
- `GET /admin/drivers?kyc_status=pending` (auth:api, role:admin)
- `GET /admin/drivers/{driver}` (auth:api, role:admin)
- `PATCH /admin/drivers/{driver}` (auth:api, role:admin)
- `POST /admin/drivers/{driver}/approve` (auth:api, role:admin)
- `POST /admin/drivers/{driver}/reject` (auth:api, role:admin)
- `GET /admin/users` (auth:api, role:admin)
- `POST /admin/users` (auth:api, role:admin)
- `GET /admin/users/{user}` (auth:api, role:admin)
- `PATCH /admin/users/{user}` (auth:api, role:admin)
- `DELETE /admin/users/{user}` (auth:api, role:admin)
- `GET /admin/rides` (auth:api, role:admin)
- `GET /admin/rides/{ride}` (auth:api, role:admin)
- `PATCH /admin/rides/{ride}` (auth:api, role:admin)
- `DELETE /admin/rides/{ride}` (auth:api, role:admin)
- `GET /admin/payments` (auth:api, role:admin)
- `GET /admin/payments/{payment}` (auth:api, role:admin)
- `PATCH /admin/payments/{payment}` (auth:api, role:admin)
- `DELETE /admin/payments/{payment}` (auth:api, role:admin)
- `GET /admin/driver-documents` (auth:api, role:admin)
- `GET /admin/driver-documents/{driverDocument}` (auth:api, role:admin)
- `PATCH /admin/driver-documents/{driverDocument}` (auth:api, role:admin)
- `DELETE /admin/driver-documents/{driverDocument}` (auth:api, role:admin)

### Webhooks
- `POST /webhooks/paystack`

### Broadcasting (Reverb)
Private channels:
- `private-ride.{rideId}`
- `private-user.{userId}`

Events:
- `ride.status.updated`
- `driver.location.updated`

### Error format
Validation: `{ "message": "...", "errors": { "field": ["..."] } }`
Auth: `{ "message": "Unauthenticated." }`
Forbidden: `{ "message": "Forbidden." }`
Conflict: `{ "message": "Ride cannot be accepted in current status." }`

### Env keys
- `JWT_SECRET`
- `PAYSTACK_PUBLIC_KEY`, `PAYSTACK_SECRET_KEY`, `PAYSTACK_WEBHOOK_SECRET`
- `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET`, `REVERB_HOST`, `REVERB_PORT`
