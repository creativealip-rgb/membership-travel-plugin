<?php
/**
 * Tour Archive Template
 * Displays list of all tours
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Query tours
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$args = [
    'post_type' => 'tour',
    'posts_per_page' => 12,
    'paged' => $paged,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
];

$tours = new WP_Query($args);
?>

<style>
.tmpb-tours-archive{max-width:1200px;margin:0 auto;padding:20px}
.tmpb-archive-header{text-align:center;margin-bottom:40px;padding-bottom:20px;border-bottom:2px solid #eee}
.tmpb-archive-header h1{font-size:2.5em;margin-bottom:10px}
.tmpb-archive-header p{color:#666;font-size:1.1em}
.tmpb-tours-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;margin-bottom:40px}
.tmpb-tour-card{background:#fff;border:1px solid #ddd;border-radius:8px;overflow:hidden;transition:transform 0.3s,box-shadow 0.3s}
.tmpb-tour-card:hover{transform:translateY(-5px);box-shadow:0 4px 12px rgba(0,0,0,0.15)}
.tmpb-tour-image{width:100%;height:200px;object-fit:cover}
.tmpb-tour-image-placeholder{width:100%;height:200px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);display:flex;align-items:center;justify-content:center;font-size:4em;color:#fff}
.tmpb-tour-content{padding:20px}
.tmpb-tour-title{margin:0 0 10px;font-size:1.3em}
.tmpb-tour-title a{color:#333;text-decoration:none}
.tmpb-tour-title a:hover{color:#0073aa}
.tmpb-tour-location{color:#666;margin:0 0 10px;font-size:0.9em}
.tmpb-tour-meta{display:flex;gap:15px;margin-bottom:15px;font-size:0.9em;color:#666}
.tmpb-tour-footer{display:flex;justify-content:space-between;align-items:center;padding-top:15px;border-top:1px solid #eee}
.tmpb-tour-price{display:flex;flex-direction:column}
.tmpb-price-label{font-size:0.8em;color:#666}
.tmpb-price-amount{font-size:1.3em;font-weight:bold;color:#0073aa}
.tmpb-btn{display:inline-block;padding:10px 20px;border-radius:4px;text-decoration:none;font-weight:600;cursor:pointer;border:none;transition:background 0.3s}
.tmpb-btn-primary{background:#0073aa;color:#fff}
.tmpb-btn-primary:hover{background:#005177;color:#fff}
.tmpb-pagination{display:flex;justify-content:center;gap:10px;margin-top:30px}
.tmpb-pagination a,.tmpb-pagination span{padding:8px 16px;border:1px solid #ddd;border-radius:4px;text-decoration:none;color:#333}
.tmpb-pagination a:hover{background:#0073aa;color:#fff}
.tmpb-pagination .current{background:#0073aa;color:#fff;border-color:#0073aa}
.tmpb-no-tours{text-align:center;padding:60px;background:#f9f9f9;border-radius:8px}
.tmpb-no-tours h2{margin-bottom:15px}
@media(max-width:768px){.tmpb-tours-grid{grid-template-columns:1fr}}
</style>

<div class="tmpb-tours-archive">
    <div class="tmpb-archive-header">
        <h1>🎫 Our Tours</h1>
        <p>Discover amazing travel experiences</p>
    </div>
    
    <?php if ($tours->have_posts()): ?>
        <div class="tmpb-tours-grid">
            <?php while ($tours->have_posts()): $tours->the_post(); 
                $tour_id = get_the_ID();
                $price = get_post_meta($tour_id, 'price', true);
                $duration = get_post_meta($tour_id, '_tour_duration_days', true);
                $location = get_post_meta($tour_id, '_tour_location', true);
                $thumbnail = get_the_post_thumbnail_url($tour_id, 'medium');
            ?>
                <div class="tmpb-tour-card">
                    <?php if ($thumbnail): ?>
                        <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php the_title(); ?>" class="tmpb-tour-image">
                    <?php else: ?>
                        <div class="tmpb-tour-image-placeholder">🗺️</div>
                    <?php endif; ?>
                    
                    <div class="tmpb-tour-content">
                        <h3 class="tmpb-tour-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        
                        <?php if ($location): ?>
                            <p class="tmpb-tour-location">📍 <?php echo esc_html($location); ?></p>
                        <?php endif; ?>
                        
                        <div class="tmpb-tour-meta">
                            <span>⏱️ <?php echo esc_html($duration); ?> days</span>
                        </div>
                        
                        <div class="tmpb-tour-footer">
                            <div class="tmpb-tour-price">
                                <span class="tmpb-price-label">From</span>
                                <span class="tmpb-price-amount">Rp <?php echo number_format(absint($price), 0, ',', '.'); ?></span>
                            </div>
                            
                            <a href="<?php the_permalink(); ?>" class="tmpb-btn tmpb-btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <?php
        // Pagination
        $big = 999999999;
        echo paginate_links([
            'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format' => '?paged=%#%',
            'current' => max(1, $paged),
            'total' => $tours->max_num_pages,
            'prev_text' => '← Previous',
            'next_text' => 'Next →',
        ]);
        ?>
        
        <?php wp_reset_postdata(); ?>
    <?php else: ?>
        <div class="tmpb-no-tours">
            <h2>No Tours Available</h2>
            <p>Check back soon for new tour packages!</p>
        </div>
    <?php endif; ?>
</div>

<?php get_footer();
