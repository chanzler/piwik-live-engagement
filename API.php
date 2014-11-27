<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\LiveEngagement;

use Piwik\Piwik;
use Piwik\API\Request;
use \DateTimeZone;
use Piwik\Site;
use Piwik\Common;


/**
 * API for plugin ConcurrentsByTrafficSource
 *
 */
class API extends \Piwik\Plugin\API {

	private static function isSocialUrl($url, $socialName = false)
	{
		foreach (Common::getSocialUrls() as $domain => $name) {
	
			if (preg_match('/(^|[\.\/])'.$domain.'([\.\/]|$)/', $url) && ($socialName === false || $name == $socialName)) {
	
				return true;
			}
		}
	
		return false;
	}
	
	private static function get_timezone_offset($remote_tz, $origin_tz = null) {
    		if($origin_tz === null) {
        		if(!is_string($origin_tz = date_default_timezone_get())) {
            			return false; // A UTC timestamp was returned -- bail out!
        		}
    		}
			if (preg_match("/^UTC[-+]*/", $origin_tz)){
				return(substr($origin_tz, 3));
    		}
    		$origin_dtz = new \DateTimeZone($origin_tz);
    		$remote_dtz = new \DateTimeZone($remote_tz);
    		$origin_dt = new \DateTime("now", $origin_dtz);
    		$remote_dt = new \DateTime("now", $remote_dtz);
    		$offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
    		return $offset;
	}
	
	private static function startsWith($haystack, $needle){
    	return $needle === "" || strpos($haystack, $needle) === 0;
	}
	
    /**
     * Retrieves visit count from lastMinutes and peak visit count from lastDays
     * in lastMinutes interval for site with idSite.
     *
     * @param int $idSite
     * @param int $lastMinutes
     * @param int $lastDays
     * @return int
     */
    public static function getLiveEngagement($idSite, $lastMinutes=20)
    {
        \Piwik\Piwik::checkUserHasViewAccess($idSite);
		$timeZoneDiff = API::get_timezone_offset('UTC', Site::getTimezoneFor($idSite));
		if (preg_match("/^UTC[-+]*/", Site::getTimezoneFor($idSite))){
			$origin_dtz = new \DateTimeZone("UTC");
			$origin_dt = new \DateTime("now", $origin_dtz);
			$origin_dt->modify( substr($origin_tz, 3).' hour' );			
    	} else {
			$origin_dtz = new \DateTimeZone(Site::getTimezoneFor($idSite));
			$origin_dt = new \DateTime("now", $origin_dtz);
    	}
		$refTime = $origin_dt->format('Y-m-d H:i:s');

        $newSql = "SELECT COUNT(DISTINCT(idvisitor))
                	FROM " . \Piwik\Common::prefixTable("log_visit") . "
        			WHERE idsite = ?
					AND DATE_SUB('".$refTime."', INTERVAL ? MINUTE) < visit_last_action_time
					AND visitor_returning = 0
                  ";
        $new = \Piwik\Db::fetchOne($newSql, array(
            $idSite, $lastMinutes+($timeZoneDiff/60)
        ));

        $returningSql = "SELECT COUNT(DISTINCT(idvisitor))
                	FROM " . \Piwik\Common::prefixTable("log_visit") . "
        			WHERE idsite = ?
					AND DATE_SUB('".$refTime."', INTERVAL ? MINUTE) < visit_last_action_time
					AND visitor_returning = 1
                ";
        $returning = \Piwik\Db::fetchOne($returningSql, array(
            $idSite, $lastMinutes+($timeZoneDiff/60)
        ));
		// more than 20 visits in the last 30 days
        $loyalSql = "SELECT COUNT(DISTINCT(idvisitor))
                	FROM " . \Piwik\Common::prefixTable("log_visit") . "
        			WHERE idsite = ?
					AND DATE_SUB('".$refTime."', INTERVAL ? MINUTE) < visit_last_action_time
        			AND visitor_returning = 1
					AND visitor_days_since_first >= 30
					AND (30 * visitor_count_visits) / visitor_days_since_first >=40
        
                ";
        $loyal = \Piwik\Db::fetchOne($loyalSql, array(
        		$idSite, $lastMinutes+($timeZoneDiff/60)
        ));
        
        $totalVisits = (int)$new+$returning;
		$newPercentage = ($totalVisits==0)?0:round($new/$totalVisits*100,2);
		$returningPercentage = ($totalVisits==0)?0:round(($returning-$loyal)/$totalVisits*100,2);
		$loyalPercentage = ($totalVisits==0)?0:round($loyal/$totalVisits*100,2);
		return array(
			array('id'=>1, 'name'=>Piwik::translate('LiveEngagement_New'), 'value'=>$new, 'percentage'=>str_replace(",", ".", sprintf("%01.2f", $newPercentage))),
			array('id'=>2, 'name'=>Piwik::translate('LiveEngagement_Returning'), 'value'=>($returning-$loyal), 'percentage'=>str_replace(",", ".", sprintf("%01.2f", $returningPercentage))),
			array('id'=>3, 'name'=>Piwik::translate('LiveEngagement_Loyal'), 'value'=>$loyal, 'percentage'=>str_replace(",", ".", sprintf("%01.2f", $loyalPercentage))),
		);
    }

}
