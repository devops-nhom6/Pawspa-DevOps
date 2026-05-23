# 🐾 Pawspa DevOps & Web Application

TEST WEBHOOK AGAIN

Dự án **Pawspa** kết hợp ứng dụng Web (PHP MVC) cùng với hệ thống DevOps hoàn chỉnh sử dụng Docker, tự động hoá CI/CD với Jenkins và giám sát tài nguyên với Prometheus & Grafana.

---

## 🛠 Tech Stack

- **Ứng dụng:** PHP (Custom MVC với Bramus Router), PDO, JWT Authentication.
- **Database:** MySQL.
- **Web Server:** Nginx.
- **CI/CD:** Jenkins (Pipeline) + Webhooks qua Ngrok.
- **Monitoring:** Prometheus, cAdvisor, Node Exporter, Grafana.
- **Containerization:** Docker & Docker Compose.

---

## 🚀 Hướng dẫn khởi chạy dự án (Local Development)

### 1. Cài đặt biến môi trường
Tạo file `.env` từ file mẫu:
```bash
cp .env.example .env
```
Tạo mã bảo mật `JWT_SECRET` (bắt buộc) và thêm vào file `.env`:
```bash
openssl rand -base64 32
```

### 2. Thiết lập Database
Dự án yêu cầu 2 file SQL để khởi tạo dữ liệu:
1. Import file cấu trúc: `lab.sql`
2. Import file dữ liệu mẫu: `seed-data.sql`

*Lưu ý: Nếu chạy bằng Docker, các file này có thể được tự động ánh xạ vào thư mục `docker-entrypoint-initdb.d` tuỳ theo cấu hình.*

### 3. Khởi chạy bằng Docker Compose (Khuyên dùng)
Dự án sử dụng project name là `du-an-web` để chia sẻ external volumes.
```bash
docker-compose -p du-an-web up -d --build
```
Hệ thống sẽ khởi chạy toàn bộ 8 services.

### 4. Khởi chạy thủ công (Không dùng Docker)
Nếu bạn chỉ muốn dev code PHP không qua Docker:
```bash
composer install
composer run dev
```
Trang web sẽ hoạt động tại: **http://localhost:8000**

---

## 🔐 Thông tin đăng nhập mặc định

**Tài khoản Quản trị hệ thống (Admin App):**
- Email: `admin@gmail.com`
- Password: `123456`

---

## 🔄 Hệ thống CI/CD (Jenkins)

Dự án được cấu hình Pipeline tự động qua file `Jenkinsfile`.
- **Luồng hoạt động:** Push Code lên Github ➡️ Webhook (Ngrok) ➡️ Jenkins ➡️ Checkout ➡️ Check Cú Pháp ➡️ Build Docker Image ➡️ Push DockerHub ➡️ Deploy lại service `app`.
- **URL Jenkins local:** `http://localhost:8082`
- **Cách kích hoạt:**
  1. Bật Ngrok: `docker run --rm -it --network host ngrok/ngrok http 8082`
  2. Gắn link Ngrok vào GitHub Webhooks với dạng `https://<url>/github-webhook/`.
  3. Đảm bảo nhánh trong Jenkins được cấu hình đúng (VD: `*/main-devops`).

---

## 📊 Hệ thống Monitoring (Giám sát)

Được tích hợp sẵn bộ 3 công cụ chuẩn DevOps để theo dõi tải hệ thống và các container:
- **Grafana:** `http://localhost:3000` (User: `admin` / Pass: `admin`)
- **Prometheus:** `http://localhost:9090` (Scrape metrics 15s/lần)
- **cAdvisor:** `http://localhost:8083` (Metrics của Docker containers)
- **Node Exporter:** `http://localhost:9100` (Metrics của WSL/Host Server)

*Dashboards đề xuất cài đặt trên Grafana: `1860` (Node Exporter Full) và `893` (Docker Monitoring).*