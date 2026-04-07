# 🚀 Worship-IO API

Worship-IO API is a comprehensive solution designed for managing **worship teams**, **church events**, and **song
setlists**.

It provides a unified management system to streamline your ministry operations, from member organization to the
technical details of your music repertoire.

---

### 🌐 Global Headers

The following headers are used across all API requests to ensure proper communication and localization:

| Header | Description | Required | Example |
| :--- | :--- | :---:| :--- |
| `Accept-Language` | The preferred language for the response. | No | `es` |
| `Accept` | Ensures the API returns JSON formatted responses. | Yes | `application/json` |
| `Authorization` | Authentication token for protected resources. | Conditional | `Bearer {token}` |

> [!TIP]
> **Internationalization support**: This API is fully localized. You can toggle between English (`en`) and Spanish
(`es`) responses simply by changing the `Accept-Language` header.

---

### 🔑 Key Features

* **Group Management**: Handle members, roles, and permissions within your worship team.
* **Song Repertoire**: Manage your songs including sections (Intro, Chorus, etc.), keys, and technical notes.
* **Event Planning**: Schedule services with full support for recurring events and attendance tracking.
* **Setlist Coordination**: Efficiently plan setlists and assign vocalists/musicians to specific songs.

---

### 🛡️ Authentication
This API uses **Laravel Sanctum** for secure authentication. Most endpoints require a valid `Bearer` token provided in
the `Authorization` header. You can obtain a token via the `Auth` endpoints.