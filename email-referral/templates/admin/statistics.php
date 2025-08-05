<div class="wrap er-section">
    <h1><?php _e('Referral Statistics', 'email-referral'); ?></h1>
    <canvas id="er-statistics-graph" width="800" height="400"></canvas>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    jQuery(document).ready(function($){
        var ctx = document.getElementById('er-statistics-graph').getContext('2d');
        var chartType = '<?php echo esc_js(get_option('er_graph_type', 'line')); ?>';
        var data = <?php
            $stats = (class_exists('ER_DB') && method_exists('ER_DB', 'get_statistics_data')) ? ER_DB::get_statistics_data() : [];
            if (empty($stats) || !isset($stats['labels'], $stats['claims'])) {
                $stats = ['labels' => [__('No data','email-referral')], 'claims' => [0]];
            }
            echo json_encode($stats);
        ?>;
        new Chart(ctx, {
            type: chartType,
            data: {
                labels: data.labels,
                datasets: [{
                    label: '<?php echo esc_js(__('Claims','email-referral')); ?>',
                    data: data.claims,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor:  'rgba(54,162,235,1)',
                    borderWidth: 1
                }]
            },
            options: {scales: {y: {beginAtZero: true}}}
        });
    });
    </script>
</div>