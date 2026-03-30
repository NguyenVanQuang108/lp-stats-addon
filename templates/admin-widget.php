<?php
/**
 * Template: Admin Dashboard Widget
 * Biến nhận được từ render_dashboard_widget():
 *   $stats['total_courses']     - Tổng số khóa học
 *   $stats['total_students']    - Tổng số học viên
 *   $stats['completed_courses'] - Số lượt hoàn thành
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="lp-stats-admin-widget">

    <div class="lp-stats-admin-grid">

        <!-- Thẻ 1: Tổng số khóa học -->
        <div class="lp-stats-admin-card lp-card-courses">
            <div class="lp-stats-icon">📚</div>
            <div class="lp-stats-number"><?php echo esc_html( number_format_i18n( $stats['total_courses'] ) ); ?></div>
            <div class="lp-stats-label"><?php esc_html_e( 'Khóa Học', 'lp-stats-addon' ); ?></div>
        </div>

        <!-- Thẻ 2: Tổng số học viên đã đăng ký -->
        <div class="lp-stats-admin-card lp-card-students">
            <div class="lp-stats-icon">👨‍🎓</div>
            <div class="lp-stats-number"><?php echo esc_html( number_format_i18n( $stats['total_students'] ) ); ?></div>
            <div class="lp-stats-label"><?php esc_html_e( 'Học Viên', 'lp-stats-addon' ); ?></div>
        </div>

        <!-- Thẻ 3: Số lượt hoàn thành khóa học -->
        <div class="lp-stats-admin-card lp-card-completed">
            <div class="lp-stats-icon">✅</div>
            <div class="lp-stats-number"><?php echo esc_html( number_format_i18n( $stats['completed_courses'] ) ); ?></div>
            <div class="lp-stats-label"><?php esc_html_e( 'Lượt Hoàn Thành', 'lp-stats-addon' ); ?></div>
        </div>

    </div>

    <p class="lp-stats-updated">
        <?php
        printf(
            /* translators: %s: date and time */
            esc_html__( 'Cập nhật: %s', 'lp-stats-addon' ),
            esc_html( current_time( 'd/m/Y H:i' ) )
        );
        ?>
    </p>

</div><!-- /.lp-stats-admin-widget -->
