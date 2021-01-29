<?php

/**
 * Template for displaying Assignments
 *
 * @since v.1.3.4
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

global $wpdb;

$per_page           = 10;
$current_page       = max(1, tutor_utils()->avalue_dot('current_page', $_GET));
$offset             = ($current_page - 1) * $per_page;

$order_filter       = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';
$search_filter      = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
//announcement's parent
$course_id          = isset($_GET['course-id']) ? sanitize_text_field($_GET['course-id']) : '';
$date_filter        = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : '';

$year               = date('Y', strtotime($date_filter));
$month              = date('m', strtotime($date_filter));
$day                = date('d', strtotime($date_filter));

$current_user       = get_current_user_id();
$assignments        = tutor_utils()->get_assignments_by_instructor(null,  compact('per_page', 'offset'));
$courses            = (current_user_can('administrator')) ? tutils()->get_courses() : tutils()->get_courses_by_instructor();

if ($assignments->count) { ?>
    <div class="tutor-dashboard-announcement-sorting-wrap">
        <div class="tutor-form-group">
            <label for="">
                <?php _e('Courses', 'tutor'); ?>
            </label>
            <select class="tutor-report-category tutor-announcement-course-sorting ignore-nice-select">

                <option value=""><?php _e('All', 'tutor'); ?></option>

                <?php if ($courses) : ?>
                    <?php foreach ($courses as $course) : ?>
                        <option value="<?php echo esc_attr($course->ID) ?>" <?php selected($course_id, $course->ID, 'selected') ?>>
                            <?php echo $course->post_title; ?>
                        </option>
                    <?php endforeach; ?>
                <?php else : ?>
                    <option value=""><?php _e('No course found', 'tutor'); ?></option>
                <?php endif; ?>
            </select>
        </div>
        <div class="tutor-form-group">
            <label><?php _e('Sort By', 'tutor'); ?></label>
            <select class="tutor-announcement-order-sorting ignore-nice-select">
                <option <?php selected($order_filter, 'ASC'); ?>><?php _e('ASC', 'tutor'); ?></option>
                <option <?php selected($order_filter, 'DESC'); ?>><?php _e('DESC', 'tutor'); ?></option>
            </select>
        </div>
        <div class="tutor-form-group tutor-announcement-datepicker">
            <label><?php _e('Submission Date', 'tutor'); ?></label>
            <input type="text" class="tutor-announcement-date-sorting" id="tutor-announcement-datepicker" value="<?php echo $date_filter; ?>" autocomplete="off" />
            <i class="tutor-icon-calendar"></i>
        </div>
    </div>

    <div class="tutor-announcement-table-wrap">
        <table class="tutor-dashboard-announcement-table" width="100%">
            <thead>
                <tr>
                    <th><?php _e('Course Name', 'tutor') ?></th>
                    <th width="15%"><?php _e('Total Mark', 'tutor') ?></th>
                    <th width="15%"><?php _e('Total Submit', 'tutor') ?></th>
                    <th width="10%">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($assignments->results as $item) {
                    $max_mark = tutor_utils()->get_assignment_option($item->ID, 'total_mark');
                    $course_id = tutor_utils()->get_course_id_by_assignment($item->ID);
                    $course_url = tutor_utils()->get_tutor_dashboard_page_permalink('assignments/course');
                    $submitted_url = tutor_utils()->get_tutor_dashboard_page_permalink('assignments/submitted');
                    $comment_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(comment_ID) FROM {$wpdb->comments} WHERE comment_type = 'tutor_assignment' AND comment_post_ID = %d", $item->ID));
                    // @TODO: assign post_meta is empty if user don't click on update button (http://prntscr.com/oax4t8) but post status is publish
                ?>
                    <tr>
                        <td>
                            <h4><?php echo esc_html($item->post_title); ?></h4>
                            <p><?php echo __('Course: ', 'tutor'); ?><a href='<?php echo esc_url($course_url . '?course_id=' . $course_id) ?>' target="_blank"><?php echo get_the_title($course_id); ?> </a></p>
                        </td>
                        <td><?php echo $max_mark ?></td>
                        <td><?php echo $comment_count ?></td>
                        <td>
                            <a href="<?php echo esc_url($submitted_url . '?assignment=' . $item->ID); ?>" class="tutor-btn bordered-btn tutor-announcement-details">
                                <?php _e('Details', 'tutor'); ?>
                            </a>
                        </td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="tutor-pagination">
        <?php

        echo paginate_links(array(
            'format' => '?current_page=%#%',
            'current' => $current_page,
            'total' => ceil($assignments->count / $per_page)
        ));
        ?>
    </div>

<?php } else {
    echo '<p>' . __('No assignment available', 'tutor') . '</p>';
}
