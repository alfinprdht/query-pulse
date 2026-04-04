<?php

namespace Alfinprdht\QueryPulse\DTO\AnalysisResult;

use Alfinprdht\QueryPulse\Support\Helpers;

class IssuesDto
{
    public function __construct(
        public string $type = '',
        public int $count = 0,
        public float $time = 0,
        public array $data = [],
        public string | null $unique_id = null,
        public string | null $fingerprint = null,
        public string | null $trace = null,
        public string | null $suggestion = null,
    ) {
        if(!empty($data) && is_array($data)) {
            $this->fingerprint = $data['sql'];
            $this->trace = $data['trace'];
            $this->unique_id = md5($data['sql'] . $data['trace']);
            $this->suggestion = Helpers::suggestion($this->type);
        }
    }
}
