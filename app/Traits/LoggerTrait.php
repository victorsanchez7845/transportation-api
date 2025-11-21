<?php
namespace App\Traits;

use App\Models\Log;

trait LoggerTrait {
    
    public function createLog($data) {
        try {
            $new_log = [
                'reservation_id' => null,
                'process_id' => null,
                'type' => 'info',
                'category' => 'no_category',
                'message' => '',
                'exception' => '',
            ];

            if( isset($data['reservation_id']) ) $new_log['reservation_id'] = $data['reservation_id'];
            if( isset($data['process_id']) ) $new_log['process_id'] = $data['process_id'];
            if( isset($data['type']) && in_array($data['type'], ['info', 'warning', 'error']) ) $new_log['type'] = $data['type'];
            if( isset($data['category']) ) $new_log['category'] = $data['category'];
            if( isset($data['message']) ) $new_log['message'] = $data['message'];
            if( isset($data['exception']) ) {
                if( gettype($data['exception']) === 'string' ) $new_log['exception'] = $data['exception'];
                else if( is_object($data['exception']) ) {
                    $exception_message = method_exists($data['exception'], 'getMessage') ? $data['exception']->getMessage() : '';
                    $exception_file = method_exists($data['exception'], 'getFile') ? $data['exception']->getFile() : '';
                    $exception_line = method_exists($data['exception'], 'getLine') ? $data['exception']->getLine() : '';

                    $new_log['exception'] = $exception_message . ". In " . $exception_file . ". at line: " . $exception_line;
                }
            }

            $log_model = new Log();
            $log_model->fill($new_log);
            $log_model->save();
        } catch(\Exception $e) {}
    }
}