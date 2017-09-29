<?php
require_once(INCLUDE_DIR.'class.plugin.php');
require_once('config.php');

class LoadBalancerPlugin extends Plugin {
    var $config_class = 'LoadBalancerConfig';

    function bootstrap() {
        Signal::connect('ticket.created', function(Ticket $ticket) {
            $config = $this->getConfig();
            if(!array_key_exists($ticket->getTeamId(), (is_array($config->get('teams'))) ? $config->get('teams') : array($config->get('teams'))))
                return;
            $statuses = array();
            foreach((is_array($config->get('status'))) ? $config->get('status') : array($config->get('status')) as $status)
                array_push($statuses, $status['ht']['id']);
            if(!$ticket->getStaffId()){
                $sql='SELECT A1.`staff_id` FROM 
                (SELECT `staff_id` FROM '.TEAM_MEMBER_TABLE.' WHERE `team_id` = ' . $ticket->getTeamId() . ' AND `staff_id` > 0) A1
                LEFT JOIN 
                (SELECT `staff_id`, COUNT(*) AS `count` FROM '.TICKET_TABLE.' WHERE `status_id` IN (' . implode(", ", $statuses) . ') GROUP BY `staff_id`) A2
                ON A1.`staff_id` = A2.`staff_id`
                LEFT JOIN
                (SELECT `staff_id`, MAX(`timestamp`) AS `timestamp` FROM '.THREAD_EVENT_TABLE.' WHERE `state` = \'assigned\' GROUP BY `staff_id`) A3
                ON A1.`staff_id` = A3.`staff_id`
                ORDER BY A2.`count` ASC, A3.`timestamp` ASC
                LIMIT 1';

                if(($res=db_query($sql)) && db_num_rows($res)){
                    while($row=db_fetch_row($res)){
                        $ticket->assignToStaff(Staff::lookup($row[0]), $config->get('message'));
                        break;
                    }
                }
            }
        });
    }
    
    function uninstall() {
        return parent::uninstall($errors);
    }
}
