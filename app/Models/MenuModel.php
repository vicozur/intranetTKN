<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuModel extends Model
{
    public function getMenuByUser($username)
    {
        return $this->db->query("
            SELECT 
                u.username, 
                t.name AS taskname,
                t.route, 
                t.icon, 
                t.parent, 
                p.name AS profilename
            FROM directory.user u
            INNER JOIN directory.assign a 
                ON u.user_id = a.user_user_id
            INNER JOIN directory.profile p 
                ON a.profile_profile_id = p.profile_id
            INNER JOIN directory.profile_has_task pht 
                ON p.profile_id = pht.profile_profile_id
            INNER JOIN directory.task t 
                ON pht.task_task_id = t.task_id
            WHERE u.username = ? 
                AND u.status = true 
                AND t.status = true
            ORDER BY t.parent ASC
        ", [$username])->getResultArray();
    }
}
