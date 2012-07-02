<?php
class progress {


    private $fields = array();
    private $defaults = array();

    public function __construct($start = 1,$total = 100,$step_size = 1,$report = 10)
    {
        $this->fields['start'] = $start;
        $this->fields['total'] = $total;
        $this->fields['step_size']  = $step_size;
        $this->fields['current_step']  = $start;
        $this->fields['previous_step']  = $start;
        $this->fields['current_percent']  = 1;
        $this->fields['previous_percent']  = 0;
        $this->fields['report']  = $report;

        $this->defaults = $this->fields;

    }

    public function step_forward()
    {
        $this->fields['previous_step']  = $this->fields['current_step'];
        $this->fields['current_step']  = $this->fields['current_step'] + $this->fields['step_size'];
        $this->percent();
    }

    public function step_to($value)
    {
        $this->fields['previous_step']  = $this->fields['current_step'];
        $this->fields['current_step']  = $value;
        $this->percent();
    }

    public function reset()
    {
        $this->fields = $this->defaults;
    }

    public function percent()
    {
        $this->fields['previous_percent'] = $this->fields['current_percent'];
        $this->fields['current_percent'] = round(($this->fields['current_step'] / $this->fields['total']) * 100.0,0);
    }

    public function display_percent()
    {
        $pcent = $this->fields['current_percent'];
        if ($pcent % $this->fields['report'] == 0 &&
            $this->fields['previous_percent'] != $this->fields['current_percent']
            )
                echo "{$pcent}% ";
    }

    public function display_step()
    {
        $pcent = $this->fields['current_percent'];
        if ($pcent % $this->fields['report'] == 0 &&
            $this->fields['previous_percent'] != $this->fields['current_percent'])
                echo "{$this->fields['current_step']}/{$this->fields['total']} ";

    }


}

?>