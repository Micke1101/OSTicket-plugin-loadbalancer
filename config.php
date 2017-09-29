<?php

require_once(INCLUDE_DIR.'/class.plugin.php');
require_once(INCLUDE_DIR.'/class.forms.php');


class LoadBalancerConfig extends PluginConfig {

    // Provide compatibility function for versions of osTicket prior to
    // translation support (v1.9.4)
    function translate() {
        if (!method_exists('Plugin', 'translate')) {
            return array(
                function($x) { return $x; },
                function($x, $y, $n) { return $n != 1 ? $y : $x; },
            );
        }
        return Plugin::translate('loadbalancer');
    }

    function getOptions() {
        list($__, $_N) = self::translate();
        return array(
            'teams' => new ChoiceField([
                'label' => $__('Teams'),
                'required' => true,
                'hint' => $__('What teams do you want to loadbalance.'),
                'configuration'=>array('multiselect'=>true,'prompt'=>__('Team')),
                'default' => '',
                'choices' => Team::getActiveTeams()
            ]),
            'status' => new ChoiceField([
                'label' => $__('Status'),
                'required' => true,
                'hint' => $__('What statuses shall be accounted for when loadbalancing.'),
                'configuration'=>array('multiselect'=>true,'prompt'=>__('Status')),
                'default' => '',
                'choices' => TicketStatusList::getStatuses()
            ]),
            'message' => new TextareaField([
                'label' => $__('Message'),
                'required' => false,
                'configuration'=>array('cols'=>50,'length'=>1024,'rows'=>4,'html'=>true),
                'hint' => $__('What message do you want to be displayed when loadbalancing.'),
                'default' => 'Loadbalancing'
            ])
        );
    }
}
