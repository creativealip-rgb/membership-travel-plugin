<?php
/**
 * My Bookings Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$booking_manager = new TMP_Booking_Manager();
$user_id = get_current_user_id();
$bookings = $booking_manager->get_user_bookings($user_id);
?>

<div class="tmpb-my-bookings">
    <h2><?php esc_html_e('My Tour Bookings', 'travel-membership-pro'); ?></h2>
    
    <?php if (empty($bookings)): ?>
        <div class="tmpb-no-bookings">
            <p><?php esc_html_e('You have not made any bookings yet.', 'travel-membership-pro'); ?></p>
            <a href="<?php echo get_post_type_archive_link('tour'); ?>" class="tmpb-btn tmpb-btn-primary">
                <?php esc_html_e('Browse Tours', 'travel-membership-pro'); ?>
            </a>
        </div>
    <?php else: ?>
        <div class="tmpb-bookings-list">
            <?php foreach ($bookings as $booking): 
                $details = $booking_manager->get_booking_details($booking->ID);
                $status = $details['status'];
                
                $status_labels = [
                    'pending_payment' => ['label' => '⏳ Pending Payment', 'class' => 'pending'],
                    'payment_uploaded' => ['label' => '📤 Payment Uploaded', 'class' => 'uploaded'],
                    'paid' => ['label' => '✅ Paid', 'class' => 'paid'],
                    'confirmed' => ['label' => '✓ Confirmed', 'class' => 'confirmed'],
                    'cancelled' => ['label' => '❌ Cancelled', 'class' => 'cancelled'],
                    'completed' => ['label' => '✔ Completed', 'class' => 'completed'],
                ];
                
                $status_info = $status_labels[$status] ?? ['label' => $status, 'class' => ''];
            ?>
                <div class="tmpb-booking-item">
                    <div class="tmpb-booking-header">
                        <div class="tmpb-booking-code">
                            <strong><?php esc_html_e('Booking Code:', 'travel-membership-pro'); ?></strong>
                            <code><?php echo esc_html($details['booking_code']); ?></code>
                        </div>
                        
                        <span class="tmpb-booking-status tmpb-status-<?php echo esc_attr($status_info['class']); ?>">
                            <?php echo esc_html($status_info['label']); ?>
                        </span>
                    </div>
                    
                    <div class="tmpb-booking-body">
                        <div class="tmpb-booking-tour">
                            <?php if ($details['tour']['thumbnail']): ?>
                                <img src="<?php echo esc_url($details['tour']['thumbnail']); ?>" alt="">
                            <?php endif; ?>
                            
                            <div>
                                <h4><?php echo esc_html($details['tour']['title']); ?></h4>
                                <p>
                                    📅 <?php esc_html_e('Travel Date:', 'travel-membership-pro'); ?> 
                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($details['travel_date']))); ?>
                                </p>
                                <p>
                                    👥 <?php echo esc_html($details['pax']); ?> <?php esc_html_e('persons', 'travel-membership-pro'); ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="tmpb-booking-footer">
                            <div class="tmpb-booking-total">
                                <strong><?php esc_html_e('Total:', 'travel-membership-pro'); ?></strong>
                                <span>Rp <?php echo number_format($details['total'], 0, ',', '.'); ?></span>
                            </div>
                            
                            <div class="tmpb-booking-actions">
                                <a href="#" class="tmpb-btn tmpb-btn-secondary tmpb-view-booking" 
                                   data-booking-id="<?php echo esc_attr($booking->ID); ?>">
                                    <?php esc_html_e('View Details', 'travel-membership-pro'); ?>
                                </a>
                                
                                <?php if ($status === 'pending_payment'): ?>
                                    <button class="tmpb-btn tmpb-btn-warning tmpb-upload-payment" 
                                            data-booking-id="<?php echo esc_attr($booking->ID); ?>">
                                        <?php esc_html_e('Upload Payment', 'travel-membership-pro'); ?>
                                    </button>
                                <?php endif; ?>
                                
                                <?php if (in_array($status, ['pending_payment', 'payment_uploaded', 'paid', 'confirmed'])): ?>
                                    <button class="tmpb-btn tmpb-btn-danger tmpb-cancel-booking" 
                                            data-booking-id="<?php echo esc_attr($booking->ID); ?>">
                                        <?php esc_html_e('Cancel', 'travel-membership-pro'); ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
