<?php

namespace PGFE_Lite\Core;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Performance Monitor for tracking and optimizing plugin performance
 * 
 * Monitors execution times, memory usage, database queries, and cache performance
 */
class PerformanceMonitor {
    
    /**
     * Performance metrics
     */
    private static $metrics = [
        'execution_times' => [],
        'memory_usage' => [],
        'db_queries' => [],
        'cache_stats' => [],
        'ajax_requests' => []
    ];
    
    /**
     * Active timers
     */
    private static $timers = [];
    
    /**
     * Performance thresholds
     */
    private static $thresholds = [
        'execution_time' => 1.0, // seconds
        'memory_usage' => 50 * 1024 * 1024, // 50MB
        'db_queries' => 20, // per request
        'cache_hit_ratio' => 0.8 // 80%
    ];
    
    /**
     * Initialize performance monitoring
     */
    public static function init() {
        if (!WP_DEBUG || !defined('PGFE_PERFORMANCE_MONITORING') || !PGFE_PERFORMANCE_MONITORING) {
            return;
        }
        
        add_action('init', [__CLASS__, 'startGlobalTimer']);
        add_action('wp_footer', [__CLASS__, 'outputPerformanceReport']);
        add_action('admin_footer', [__CLASS__, 'outputPerformanceReport']);
        add_action('wp_ajax_pgfe_performance_report', [__CLASS__, 'ajaxPerformanceReport']);
        add_action('wp_ajax_nopriv_pgfe_performance_report', [__CLASS__, 'ajaxPerformanceReport']);
        
        // Hook into WordPress query monitoring
        add_filter('query', [__CLASS__, 'monitorQuery']);
    }
    
    /**
     * Start a performance timer
     * 
     * @param string $name
     */
    public static function startTimer($name) {
        self::$timers[$name] = [
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(true)
        ];
    }
    
    /**
     * Stop a performance timer
     * 
     * @param string $name
     * @return array|null
     */
    public static function stopTimer($name) {
        if (!isset(self::$timers[$name])) {
            return null;
        }
        
        $timer = self::$timers[$name];
        $end_time = microtime(true);
        $end_memory = memory_get_usage(true);
        
        $metrics = [
            'name' => $name,
            'execution_time' => $end_time - $timer['start_time'],
            'memory_used' => $end_memory - $timer['start_memory'],
            'timestamp' => current_time('mysql')
        ];
        
        self::$metrics['execution_times'][] = $metrics;
        
        // Check thresholds
        self::checkThresholds($metrics);
        
        unset(self::$timers[$name]);
        
        return $metrics;
    }
    
    /**
     * Start global timer for page load
     */
    public static function startGlobalTimer() {
        self::startTimer('page_load');
    }
    
    /**
     * Monitor database queries
     * 
     * @param string $query
     * @return string
     */
    public static function monitorQuery($query) {
        // Only monitor PGFE-related queries
        if (strpos($query, 'pgfe') !== false || strpos($query, 'woocommerce') !== false) {
            self::$metrics['db_queries'][] = [
                'query' => $query,
                'timestamp' => microtime(true),
                'backtrace' => wp_debug_backtrace_summary()
            ];
        }
        
        return $query;
    }
    
    /**
     * Record AJAX request performance
     * 
     * @param string $action
     * @param float $execution_time
     * @param int $memory_used
     */
    public static function recordAjaxRequest($action, $execution_time, $memory_used) {
        self::$metrics['ajax_requests'][] = [
            'action' => $action,
            'execution_time' => $execution_time,
            'memory_used' => $memory_used,
            'timestamp' => current_time('mysql')
        ];
    }
    
    /**
     * Record cache statistics
     * 
     * @param string $operation
     * @param bool $hit
     * @param string $key
     */
    public static function recordCacheOperation($operation, $hit, $key = '') {
        self::$metrics['cache_stats'][] = [
            'operation' => $operation,
            'hit' => $hit,
            'key' => $key,
            'timestamp' => microtime(true)
        ];
    }
    
    /**
     * Check performance thresholds
     * 
     * @param array $metrics
     */
    private static function checkThresholds($metrics) {
        $warnings = [];
        
        if ($metrics['execution_time'] > self::$thresholds['execution_time']) {
            $warnings[] = "Slow execution: {$metrics['name']} took {$metrics['execution_time']}s";
        }
        
        if ($metrics['memory_used'] > self::$thresholds['memory_usage']) {
            $memory_mb = round($metrics['memory_used'] / 1024 / 1024, 2);
            $warnings[] = "High memory usage: {$metrics['name']} used {$memory_mb}MB";
        }
        
        if (!empty($warnings)) {
            foreach ($warnings as $warning) {
                // Avertissement de performance géré
            }
        }
    }
    
    /**
     * Get performance report
     * 
     * @return array
     */
    public static function getPerformanceReport() {
        $report = [
            'summary' => self::generateSummary(),
            'execution_times' => self::$metrics['execution_times'],
            'memory_usage' => self::getMemoryReport(),
            'database_queries' => self::getDatabaseReport(),
            'cache_performance' => self::getCacheReport(),
            'ajax_requests' => self::$metrics['ajax_requests'],
            'recommendations' => self::generateRecommendations()
        ];
        
        return $report;
    }
    
    /**
     * Generate performance summary
     * 
     * @return array
     */
    private static function generateSummary() {
        $total_execution_time = array_sum(array_column(self::$metrics['execution_times'], 'execution_time'));
        $total_memory_used = array_sum(array_column(self::$metrics['execution_times'], 'memory_used'));
        $total_queries = count(self::$metrics['db_queries']);
        
        return [
            'total_execution_time' => round($total_execution_time, 4),
            'total_memory_used' => round($total_memory_used / 1024 / 1024, 2), // MB
            'total_db_queries' => $total_queries,
            'peak_memory_usage' => round(memory_get_peak_usage(true) / 1024 / 1024, 2), // MB
            'current_memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2), // MB
            'cache_hit_ratio' => self::calculateCacheHitRatio()
        ];
    }
    
    /**
     * Get memory usage report
     * 
     * @return array
     */
    private static function getMemoryReport() {
        return [
            'peak_usage' => memory_get_peak_usage(true),
            'current_usage' => memory_get_usage(true),
            'limit' => ini_get('memory_limit'),
            'usage_by_function' => self::$metrics['execution_times']
        ];
    }
    
    /**
     * Get database queries report
     * 
     * @return array
     */
    private static function getDatabaseReport() {
        $queries = self::$metrics['db_queries'];
        $slow_queries = array_filter($queries, function($query) {
            return isset($query['execution_time']) && $query['execution_time'] > 0.1;
        });
        
        return [
            'total_queries' => count($queries),
            'slow_queries' => count($slow_queries),
            'queries' => $queries,
            'slow_query_details' => $slow_queries
        ];
    }
    
    /**
     * Get cache performance report
     * 
     * @return array
     */
    private static function getCacheReport() {
        $cache_stats = self::$metrics['cache_stats'];
        $hits = array_filter($cache_stats, function($stat) {
            return $stat['hit'] === true;
        });
        
        $total_operations = count($cache_stats);
        $hit_ratio = $total_operations > 0 ? count($hits) / $total_operations : 0;
        
        return [
            'total_operations' => $total_operations,
            'cache_hits' => count($hits),
            'cache_misses' => $total_operations - count($hits),
            'hit_ratio' => round($hit_ratio, 2),
            'operations' => $cache_stats
        ];
    }
    
    /**
     * Calculate cache hit ratio
     * 
     * @return float
     */
    private static function calculateCacheHitRatio() {
        $cache_stats = self::$metrics['cache_stats'];
        
        if (empty($cache_stats)) {
            return 0;
        }
        
        $hits = array_filter($cache_stats, function($stat) {
            return $stat['hit'] === true;
        });
        
        return round(count($hits) / count($cache_stats), 2);
    }
    
    /**
     * Generate performance recommendations
     * 
     * @return array
     */
    private static function generateRecommendations() {
        $recommendations = [];
        $summary = self::generateSummary();
        
        // Check execution time
        if ($summary['total_execution_time'] > self::$thresholds['execution_time']) {
            $recommendations[] = [
                'type' => 'performance',
                'severity' => 'warning',
                'message' => 'Total execution time is high. Consider optimizing slow functions.',
                'details' => 'Current: ' . $summary['total_execution_time'] . 's, Threshold: ' . self::$thresholds['execution_time'] . 's'
            ];
        }
        
        // Check memory usage
        if ($summary['peak_memory_usage'] > (self::$thresholds['memory_usage'] / 1024 / 1024)) {
            $recommendations[] = [
                'type' => 'memory',
                'severity' => 'warning',
                'message' => 'High memory usage detected. Consider optimizing memory-intensive operations.',
                'details' => 'Peak usage: ' . $summary['peak_memory_usage'] . 'MB'
            ];
        }
        
        // Check database queries
        if ($summary['total_db_queries'] > self::$thresholds['db_queries']) {
            $recommendations[] = [
                'type' => 'database',
                'severity' => 'warning',
                'message' => 'High number of database queries. Consider implementing caching or query optimization.',
                'details' => 'Total queries: ' . $summary['total_db_queries']
            ];
        }
        
        // Check cache performance
        if ($summary['cache_hit_ratio'] < self::$thresholds['cache_hit_ratio']) {
            $recommendations[] = [
                'type' => 'cache',
                'severity' => 'info',
                'message' => 'Cache hit ratio is below optimal. Consider reviewing cache strategy.',
                'details' => 'Hit ratio: ' . ($summary['cache_hit_ratio'] * 100) . '%'
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * Output performance report in footer
     */
    public static function outputPerformanceReport() {
        // Rapport de performance désactivé pour la production
        return;
    }
    
    /**
     * AJAX handler for performance report
     */
    public static function ajaxPerformanceReport() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $report = self::getPerformanceReport();
        wp_send_json_success($report);
    }
    
    /**
     * Get slow functions
     * 
     * @param float $threshold
     * @return array
     */
    public static function getSlowFunctions($threshold = 0.1) {
        return array_filter(self::$metrics['execution_times'], function($metric) use ($threshold) {
            return $metric['execution_time'] > $threshold;
        });
    }
    
    /**
     * Get memory-intensive functions
     * 
     * @param int $threshold
     * @return array
     */
    public static function getMemoryIntensiveFunctions($threshold = 10485760) { // 10MB
        return array_filter(self::$metrics['execution_times'], function($metric) use ($threshold) {
            return $metric['memory_used'] > $threshold;
        });
    }
    
    /**
     * Clear performance metrics
     */
    public static function clearMetrics() {
        self::$metrics = [
            'execution_times' => [],
            'memory_usage' => [],
            'db_queries' => [],
            'cache_stats' => [],
            'ajax_requests' => []
        ];
        self::$timers = [];
    }
    
    /**
     * Export performance data
     * 
     * @return string
     */
    public static function exportPerformanceData() {
        $data = [
            'timestamp' => current_time('mysql'),
            'site_url' => get_site_url(),
            'wp_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'performance_data' => self::getPerformanceReport()
        ];
        
        return wp_json_encode($data, JSON_PRETTY_PRINT);
    }
    
    /**
     * Set performance threshold
     * 
     * @param string $metric
     * @param mixed $value
     */
    public static function setThreshold($metric, $value) {
        if (isset(self::$thresholds[$metric])) {
            self::$thresholds[$metric] = $value;
        }
    }
    
    /**
     * Get current thresholds
     * 
     * @return array
     */
    public static function getThresholds() {
        return self::$thresholds;
    }
}