<div class="wrap er-section">
    <h1><?php _e('Referral Statistics', 'email-referral'); ?></h1>
    <canvas id="er-statistics-graph" width="800" height="400"></canvas>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    jQuery(document).ready(function($){
        var ctx = document.getElementById('er-statistics-graph').getContext('2d');
        var chartType = '<?php echo esc_js(get_option('er_graph_type', 'line')); ?>';
        var data = <?php echo json_encode(ER_DB::get_statistics_data()); ?>;
        new Chart(ctx, {
            type: chartType,
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Claims',
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