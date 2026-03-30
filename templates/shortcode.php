<?php
/**
 * Template: Shortcode [lp_total_stats] - Frontend
 * Biến nhận được từ shortcode_render():
 *   $atts['show_title']         - 'yes' | 'no'
 *   $stats['total_courses']
 *   $stats['total_students']
 *   $stats['completed_courses']
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="lp-stats-shortcode-wrapper">

    <?php if ( 'yes' === $atts['show_title'] ) : ?>
        <h3 class="lp-stats-title">
            <?php esc_html_e( '📊 Thống Kê Học Tập', 'lp-stats-addon' ); ?>
        </h3>
    <?php endif; ?>

    <div class="lp-stats-cards-row">

        <div class="lp-stats-card lp-courses-card">
            <span class="lp-stat-emoji">📚</span>
            <span class="lp-stat-value"><?php echo esc_html( number_format_i18n( $stats['total_courses'] ) ); ?></span>
            <span class="lp-stat-name"><?php esc_html_e( 'Khóa Học', 'lp-stats-addon' ); ?></span>
        </div>

        <div class="lp-stats-card lp-students-card">
            <span class="lp-stat-emoji">👨‍🎓</span>
            <span class="lp-stat-value"><?php echo esc_html( number_format_i18n( $stats['total_students'] ) ); ?></span>
            <span class="lp-stat-name"><?php esc_html_e( 'Học Viên', 'lp-stats-addon' ); ?></span>
        </div>

        <div class="lp-stats-card lp-completed-card">
            <span class="lp-stat-emoji">✅</span>
            <span class="lp-stat-value"><?php echo esc_html( number_format_i18n( $stats['completed_courses'] ) ); ?></span>
            <span class="lp-stat-name"><?php esc_html_e( 'Lượt Hoàn Thành', 'lp-stats-addon' ); ?></span>
        </div>

    </div><!-- /.lp-stats-cards-row -->

</div><!-- /.lp-stats-shortcode-wrapper -->
