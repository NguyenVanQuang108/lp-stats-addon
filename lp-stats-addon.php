<?php
/**
 * Plugin Name:       LearnPress Stats Dashboard
 * Plugin URI:        https://github.com/example/lp-stats-addon
 * Description:       Hiển thị bảng thống kê LearnPress trong Admin Dashboard và Frontend Shortcode. Addon không chỉnh sửa code LearnPress gốc.
 * Version:           1.0.0
 * Author:            Sinh vien WordPress
 * Author URI:        https://example.com
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       lp-stats-addon
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      7.4
 */

// Bảo vệ: không cho phép truy cập trực tiếp vào file plugin
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// CONSTANTS
// =============================================================================
define( 'LP_STATS_VERSION',     '1.0.0' );
define( 'LP_STATS_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'LP_STATS_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );

// =============================================================================
// CLASS CHÍNH CỦA PLUGIN
// =============================================================================
if ( ! class_exists( 'LP_Stats_Addon' ) ) :

class LP_Stats_Addon {

    /**
     * Phiên bản plugin
     * @var string
     */
    public $version = LP_STATS_VERSION;

    /**
     * Instance singleton
     * @var LP_Stats_Addon|null
     */
    private static $instance = null;

    /**
     * Trả về instance duy nhất (Singleton Pattern)
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor - đăng ký các hooks
     */
    private function __construct() {
        add_action( 'init',            array( $this, 'load_textdomain' ) );
        add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widget' ) );
        add_action( 'wp_enqueue_scripts',  array( $this, 'enqueue_frontend_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        add_shortcode( 'lp_total_stats',   array( $this, 'shortcode_render' ) );
    }

    /**
     * Load file ngôn ngữ (i18n)
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'lp-stats-addon',
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/languages/'
        );
    }

    // =========================================================================
    // PHẦN 1: LẤY DỮ LIỆU THỐNG KÊ
    // =========================================================================

    /**
     * Lấy tổng số khóa học (Course) hiện có.
     *
     * Ưu tiên dùng hàm native của LearnPress nếu tồn tại.
     * Fallback về $wpdb nếu LearnPress chưa khởi tạo.
     *
     * @return int Tổng số khóa học đã xuất bản
     */
    public function get_total_courses() {
        // Cách 1: Dùng WP_Query - không phụ thuộc DB schema LearnPress
        $query = new WP_Query( array(
            'post_type'      => LP_COURSE_CPT,   // 'lp_course' - hằng số LearnPress
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',           // Chỉ lấy ID để tiết kiệm bộ nhớ
            'no_found_rows'  => false,
        ) );
        return (int) $query->found_posts;
    }

    /**
     * Lấy tổng số học viên đã đăng ký ít nhất 1 khóa học.
     *
     * Truy vấn bảng wp_learnpress_user_items:
     *   - item_type = 'lp_course' : chỉ lấy bản ghi khóa học (không lấy lesson/quiz)
     *   - COUNT(DISTINCT user_id)  : đếm học viên duy nhất (tránh đếm trùng)
     *
     * @return int Tổng số học viên duy nhất đã đăng ký
     */
    public function get_total_enrolled_students() {
        global $wpdb;

        // Tên bảng đầy đủ, tương thích với table prefix tùy chỉnh
        $table = $wpdb->prefix . 'learnpress_user_items';

        // Kiểm tra bảng tồn tại (tránh lỗi khi LearnPress chưa cài)
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) !== $table ) {
            return 0;
        }

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT user_id)
                 FROM {$table}
                 WHERE item_type = %s",
                LP_COURSE_CPT   // 'lp_course'
            )
        );

        return (int) $count;
    }

    /**
     * Lấy số lượng khóa học đã được hoàn thành (graduation = 'completed').
     *
     * Truy vấn bảng wp_learnpress_user_items:
     *   - item_type  = 'lp_course'
     *   - graduation = 'completed'  (trạng thái tốt nghiệp/hoàn thành)
     *
     * Mỗi hàng là 1 lượt học viên hoàn thành 1 khóa học.
     *
     * @return int Tổng số lượt hoàn thành khóa học
     */
    public function get_total_completed_courses() {
        global $wpdb;

        $table = $wpdb->prefix . 'learnpress_user_items';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) !== $table ) {
            return 0;
        }

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*)
                 FROM {$table}
                 WHERE item_type  = %s
                   AND graduation = %s",
                LP_COURSE_CPT,  // 'lp_course'
                'completed'
            )
        );

        return (int) $count;
    }

    /**
     * Gộp tất cả số liệu thành 1 mảng để dễ sử dụng
     *
     * @return array Mảng chứa total_courses, total_students, completed_courses
     */
    public function get_all_stats() {
        return array(
            'total_courses'     => $this->get_total_courses(),
            'total_students'    => $this->get_total_enrolled_students(),
            'completed_courses' => $this->get_total_completed_courses(),
        );
    }

    // =========================================================================
    // PHẦN 2: ENQUEUE STYLES
    // =========================================================================

    /**
     * Nạp CSS cho Frontend
     */
    public function enqueue_frontend_styles() {
        wp_enqueue_style(
            'lp-stats-addon-frontend',
            LP_STATS_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            LP_STATS_VERSION
        );
    }

    /**
     * Nạp CSS cho Admin
     */
    public function enqueue_admin_styles() {
        wp_enqueue_style(
            'lp-stats-addon-admin',
            LP_STATS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            LP_STATS_VERSION
        );
    }

    // =========================================================================
    // PHẦN 3: ADMIN DASHBOARD WIDGET
    // =========================================================================

    /**
     * Đăng ký Dashboard Widget trong trang quản trị WordPress
     * Hook: wp_dashboard_setup
     */
    public function register_dashboard_widget() {
        wp_add_dashboard_widget(
            'lp_stats_dashboard_widget',           // Widget ID (duy nhất)
            '📊 ' . __( 'LearnPress Thống Kê Nhanh', 'lp-stats-addon' ), // Tiêu đề
            array( $this, 'render_dashboard_widget' )  // Callback render nội dung
        );
    }

    /**
     * Render nội dung Dashboard Widget
     */
    public function render_dashboard_widget() {
        // Kiểm tra LearnPress đã được cài và kích hoạt
        if ( ! function_exists( 'LearnPress' ) && ! defined( 'LP_COURSE_CPT' ) ) {
            echo '<p style="color:#e74c3c;">';
            esc_html_e( '⚠️ LearnPress chưa được kích hoạt. Vui lòng cài đặt và kích hoạt plugin LearnPress.', 'lp-stats-addon' );
            echo '</p>';
            return;
        }

        $stats = $this->get_all_stats();
        include LP_STATS_PLUGIN_DIR . 'templates/admin-widget.php';
    }

    // =========================================================================
    // PHẦN 4: SHORTCODE [lp_total_stats]
    // =========================================================================

    /**
     * Render Shortcode [lp_total_stats] ra Frontend
     *
     * Cách dùng: thêm [lp_total_stats] vào bất kỳ post/page nào.
     * Hỗ trợ attribute:
     *   - show_title="yes|no"  (mặc định: yes)
     *
     * @param  array  $atts    Mảng attributes từ shortcode
     * @param  string $content Nội dung giữa thẻ (nếu có)
     * @return string          HTML output
     */
    public function shortcode_render( $atts, $content = null ) {
        // Kiểm tra LearnPress
        if ( ! defined( 'LP_COURSE_CPT' ) ) {
            return '<p class="lp-stats-error">'
                . esc_html__( 'LearnPress chưa được kích hoạt.', 'lp-stats-addon' )
                . '</p>';
        }

        // Merge attributes với giá trị mặc định
        $atts = shortcode_atts(
            array(
                'show_title' => 'yes',
            ),
            $atts,
            'lp_total_stats'
        );

        $stats = $this->get_all_stats();

        // Output buffering: capture template thay vì echo trực tiếp
        ob_start();
        include LP_STATS_PLUGIN_DIR . 'templates/shortcode.php';
        return ob_get_clean();
    }

} // end class LP_Stats_Addon

endif;

// =============================================================================
// KHỞI ĐỘNG PLUGIN
// =============================================================================

/**
 * Hàm truy cập global instance của plugin (giống cách LearnPress() làm)
 *
 * @return LP_Stats_Addon
 */
function LP_Stats_Addon() {
    return LP_Stats_Addon::instance();
}

// Kích hoạt plugin sau khi tất cả plugins được load
add_action( 'plugins_loaded', 'LP_Stats_Addon' );
