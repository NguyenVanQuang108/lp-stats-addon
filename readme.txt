=== LearnPress Stats Dashboard ===
Contributors: sinhvien
Tags: learnpress, stats, dashboard, lms, widget
Requires at least: 5.8
Tested up to: 6.5
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Hiển thị bảng thống kê LearnPress trong Admin Dashboard và Frontend Shortcode.

== Description ==

Plugin **LearnPress Stats Dashboard** (lp-stats-addon) là một addon mở rộng cho LearnPress LMS.
Plugin KHÔNG chỉnh sửa bất kỳ file nào của LearnPress gốc.

**Tính năng:**

* 📊 Dashboard Widget trong Admin — xem nhanh 3 chỉ số chính.
* 🔢 Shortcode `[lp_total_stats]` để nhúng thống kê vào bất kỳ trang/bài viết nào.
* Tự động phát hiện nếu LearnPress chưa được kích hoạt và hiển thị cảnh báo.

**Số liệu hiển thị:**

1. Tổng số Khóa học (post_type = lp_course, status = publish)
2. Tổng số Học viên đã đăng ký (COUNT DISTINCT user_id trong bảng learnpress_user_items)
3. Số lượt Hoàn thành Khóa học (graduation = 'completed')

== Installation ==

1. Upload thư mục `lp-stats-addon` vào `/wp-content/plugins/`.
2. Kích hoạt plugin trong **Plugins > Installed Plugins**.
3. Đảm bảo plugin LearnPress đã được cài đặt và kích hoạt trước.
4. Vào **Dashboard** để xem Widget thống kê.
5. Thêm shortcode `[lp_total_stats]` vào bất kỳ trang nào để hiển thị frontend.

== Shortcode Usage ==

`[lp_total_stats]`
`[lp_total_stats show_title="no"]`   — Ẩn tiêu đề

== Changelog ==

= 1.0.0 =
* Ra mắt phiên bản đầu tiên.
* Dashboard Widget cho Admin.
* Shortcode [lp_total_stats] cho Frontend.
* CSS responsive cho cả Admin và Frontend.

== Frequently Asked Questions ==

= Plugin có cần cài gì thêm không? =
Chỉ cần LearnPress đã được cài và kích hoạt.

= Dữ liệu được lấy từ đâu? =
Từ bảng `wp_learnpress_user_items` trong database WordPress/LearnPress.
