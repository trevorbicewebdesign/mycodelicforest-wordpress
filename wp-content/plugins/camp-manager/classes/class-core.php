<?php

class CampManagerCore {
    public function __construct()
    {
 
    }

    public function init()
    {
        register_activation_hook(__FILE__, function() {
            flush_rewrite_rules();
        });
        
        register_deactivation_hook(__FILE__, function() {
            flush_rewrite_rules();
        });        
        
    }

    public function getItemCategories(): array
    {
        return [
            'power' => 'Anything related to electricity generation or distribution (e.g. generators, cords, lights, solar, batteries)',
            'sojourner' => 'Items related to our school bus (maintenance, upgrades, fuel, storage, hardware)',
            'sound' => 'Audio/music/DJ gear (speakers, mixers, cables, microphones)',
            'shwag & print' => 'Merchandise, stickers, flyers, posters, etc.',
            'misc' => 'Doesn’t clearly fit the above categories',
        ];
    }


}
