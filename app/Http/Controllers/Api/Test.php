<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Library\Httpcurl;
use App\Models\ShHous;
use App\Service\House;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Test extends Controller
{
    protected $house;
    function __construct(House $house){
        $this->house = $house;
    }

    /**
     * 执行抓取贝壳二手房队列
     * @return json
     */
    public function setShHouse(Request $request){
        $url = $request->get('url');
        if($url == '') $url = 'https://hk.ke.com/ershoufang/';
        return $this->house->setShHouse($url);
    }

    /**
     * 抓取贝壳二手房单页数据（单线程）
     * @param Request $request
     * @return array
     */
    public function getOnePageHouse(Request $request){
        $args = $request->all();
        $args['page'] = isset($args['page']) && intval($args['page'])?intval($args['page']):1;
        $args['url'] = $args['url'] ?? 'https://hk.ke.com/ershoufang/';
        return $this->house->getOnePageHouse($args);
    }

    /**
     * 抓取贝壳二手房单页数据（多线程）
     * @param Request $request
     * @return array
     */
    public function getOnePageHouseMultiThread(Request $request){
        $args = $request->all();
        $args['page'] = isset($args['page']) && intval($args['page'])?intval($args['page']):1;
        $args['url'] = $args['url'] ?? 'https://hk.ke.com/ershoufang/';
        return $this->house->getOnePageHouseMultiThread($args);
    }

    /**
     * 测试方法-获取列表
     * @return array
     */
    public function getShList(){
        return $this->house->getShList();
    }

    /**
     * 测试专用
     * @param Request $request
     * @return array
     */
    public function getOnePageHouseTest(Request $request){
        $page = $request->get('p');
        if($page == '' || $page == 0) $page = 1;
        $url = 'https://hk.ke.com/ershoufang/';
        if($page != 1){
            $url = $url . 'pg' . $page . '/';
        }
        $res = Httpcurl::get($url,0,5);
        $i = 1;
        //如果网页下载失败，尝试重新下载 共3次
        while($res[1]['http_code'] != 200 && $i <= 3){
            $res = Httpcurl::get($url,0,5);
            if($i < 3) $i++;
        }
        if($res[1]['http_code'] != 200){
            Log::warning('请求贝壳二手房错误  url: ' . $url . ' date: ' . date('Y-m-d H:i:s',time()));
            return ['code' => 1,'msg' => '请求贝壳二手房错误','err' => $res[1]];
        }
        $html = $res[0];
        $html = preg_replace('/[\t\n\r]+/','',$html);
        $partern = '/<ul class="sellListContent" log-mod="list">.*?<\/ul>/';
        $res = preg_match($partern,$html,$arr);
        if(!$res) return ['code' => 1,'msg' => '正则匹配失败(46)'];
        $html = $arr[0];
        $html = preg_replace('/\s(?=\s)/', '', $html);
        $html = preg_replace('/\'/', '"', $html);
        $partern = '/<a class="VIEWDATA CLICKDATA maidian-detail" data-hreftype="" title="([^<>]+)" data-maidian="([^<>]+)" href="([^<>]+)" target="_blank" data-click-evtid="([^<>]+)" data-click-event="SearchClick" data-action="([^<>]+)">([^<>]+)<\/a>/';
        $res = preg_match_all($partern,$html,$arr);
        if(!$res) return ['code' => 1,'msg' => '正则匹配失败(52)'];
        $hrefArr = $arr[3];
        foreach($hrefArr as $k => $v){
            $res = Httpcurl::get($v,0,5);
            $i = 1;
            //如果网页下载失败，尝试重新下载 共3次
            while($res[1]['http_code'] != 200 && $i <= 3){
                $res = Httpcurl::get($v,0,5);
                if($i < 3) $i++;
            }
            if($res[1]['http_code'] != 200){
                Log::warning('请求贝壳二手房房源详情失败  url: ' . $v . ' date: ' . date('Y-m-d H:i:s',time()));
                return ['code' => 1,'msg' => '请求贝壳二手房房源详情失败','err' => $res[1]];
            }
            $html = $res[0];
            return $html;
            $html = preg_replace("/[\s\t\n\r]+/","",$html);
            $arr = [];

            //开始匹配房源详情----------
            //概述
            $partern = '/<h1class="main"title="([^<>]+)">([^<>]+)<\/h1>/';
            $res = preg_match_all($partern,$html,$arr);
            if($res){
                $r['desc'] = $arr[1][0];
            }else{
                Log::warning('匹配房源概述失败 url: ' . $v . ' date: ' . date('Y-m-d H:i:s'));
                $r['desc'] = '';
            }

            //价格
            $partern = '/<spanclass="total">([^<>]+)<\/span>/';
            $res = preg_match_all($partern,$html,$arr);
            if($res){
                $r['price'] = $arr[1][0];
            }else{
                Log::warning('匹配房源价格失败 url: ' . $v .' date: ' . date('Y-m-d H:i:s'));
                $r['price'] = 0.00;
            }

            //占地
            $partern = '/<spanclass="unitPriceValue">([^<>]+)<\/span>/';
            $res = preg_match_all($partern,$html,$arr);
            if($res){
                $r['area'] = $arr[1][0];
            }else{
                Log::warning('匹配房源占地面积失败 url: ' . $v .' date: ' . date('Y-m-d H:i:s'));
                $r['area'] = 0.00;
            }

            //主要信息
//            $partern = '/<divclass="mainInfo">([^<>]+)<\/div>/';
//            $res = preg_match_all($partern,$html,$arr);
//            if($res){
//                $r['main_info'] = $arr[1];
//            }else{
//                Log::warning('匹配房源匹配基本信息失败1 url: ' . $v .' date: ' . date('Y-m-d H:i:s'));
//                $r['main_info'] = [];
//            }
//            $partern = '/<divclass="mainInfo"title="([^<>]+)">([^<>]+)<\/div>/';
//            $res = preg_match_all($partern,$html,$arr);
//            if($res){
//                $r['main_info'][] = $arr[1][0];
//            }else{
//                $r['main_info'][] = '';
//                Log::warning('匹配房源基本信息失败2 url: ' . $v .' date: ' . date('Y-m-d H:i:s'));
//            }
//            $partern = '/<divclass="subInfo">([^<>]+)<\/div>/';
//            $res = preg_match_all($partern,$html,$arr);
//            if($res){
//                $r['main_info'][] = $arr[1][0];
//                $r['main_info'][] = $arr[1][1];
//                $r['main_info'][] = $arr[1][2];
//            }
//            $r['main_info'] = json_encode($r['main_info']);

            //小区名称
//            $partern = '/<ahref="([^<>]+)"class="infono_resblock_a"title="([^<>]+)">([^<>]+)<\/a>/';
//            $res = preg_match_all($partern,$html,$arr);
//            if($res){
//                $r['community_name'] = $arr[3][0];
//            }else{
//                $r['community_name'] = '';
//                Log::warning('匹配房源小区名称失败 url: ' . $v .' date: ' . date('Y-m-d H:i:s'));
//            }

            //所在区域信息
            $partern = '/<spanclass="info"><ahref="([^<>]+)"target="_blank">([^<>]+)<\/a>&nbsp;<ahref="([^<>]+)"target="_blank">([^<>]+)<\/a>&nbsp;<\/span>/';
            $res = preg_match_all($partern,$html,$arr);
            if($res){
                $r['area_name'] = $arr[2][0];
                $r['area_name'] .= $arr[4][0];
            }else{
                $r['area_name'] = '';
                Log::warning('匹配房源所在区域信息失败 url: ' . $v .' date: ' . date('Y-m-d H:i:s'));
            }

            //看房时间
            $partern = '/<spanclass="info">([^<>]+)<\/span>/';
            $res = preg_match_all($partern,$html,$arr);
            if($res){
                $r['visit_time'] = $arr[1][0];
            }else{
                Log::warning('匹配房源看房时间失败 url: ' . $v .' date: ' . date('Y-m-d H:i:s'));
                $r['visit_time'] = '';
            }

            //基本信息
            $partern = '/<li><spanclass="label">([^<>]+)<\/span>([^<>]+)<\/li>/';
            $res = preg_match_all($partern,$html,$arr);
            if($res){
                $i = 0;
                $r['base_info'] = [];
                while(isset($arr[0][$i])){
                    switch($arr[1][$i]){
                        case '房屋户型':
                            $r['base_info']['house_type'] = $arr[2][$i];
                            continue;
                        case '所在楼层':
                            $r['base_info']['on_floor'] = $arr[2][$i];
                            continue;
                        case '建筑面积':
                            $r['base_info']['area'] = $arr[2][$i];
                            continue;
                        case '户型结构':
                            $r['base_info']['house_type_structure'] = $arr[2][$i];
                            continue;
                        case '建筑类型':
                            $r['base_info']['build_type'] = $arr[2][$i];
                            continue;
                        case '房屋朝向':
                            $r['base_info']['orientation'] = $arr[2][$i];
                            continue;
                        case '建筑结构':
                            $r['base_info']['structure'] = $arr[2][$i];
                            continue;
                        case '装修情况':
                            $r['base_info']['decoration'] = $arr[2][$i];
                            continue;
                        case '梯户比例':
                            $r['base_info']['lh_ratio'] = $arr[2][$i];
                            continue;
                        case '别墅类型':
                            $r['base_info']['villa_type'] = $arr[2][$i];
                            continue;
                        case '产权年限':
                            $r['base_info']['pr_year'] = $arr[2][$i];
                            continue;
                        case '挂牌时间':
                            $r['base_info']['hang_out_date'] = $arr[2][$i];
                            continue;
                        case '交易权属':
                            $r['base_info']['trading_right'] = $arr[2][$i];
                            continue;
                        case '上次交易':
                            $r['base_info']['last_deal'] = $arr[2][$i];
                            continue;
                        case '房屋用途':
                            $r['base_info']['house_use'] = $arr[2][$i];
                            continue;
                        case '房屋年限':
                            $r['base_info']['house_life'] = $arr[2][$i];
                            continue;
                        case '产权所属':
                            $r['base_info']['property_right'] = $arr[2][$i];
                            continue;
                        case '抵押信息':
                            $r['base_info']['mortgage'] = $arr[2][$i];
                            continue;
                        case '房本备件':
                            $r['base_info']['spare_part'] = $arr[2][$i];
                            continue;
                    }
                    $i++;
                }
            }else{
                $r['base_info'] = [
                    'house_type' => '',
                    'on_floor' => '',
                    'area' => '',
                    'house_type_structure' => '',
                    'build_type' => '',
                    'orientation' => '',
                    'decoration' => '',
                    'lh_ratio' => '',
                    'villa_type' => '',
                    'pr_year' => '',
                    'hang_out_date' => '',
                    'trading_right' => '',
                    'last_deal' => '',
                    'house_use' => '',
                    'house_life' => '',
                    'property_right' => '',
                    'mortgage' => '',
                    'spare_part' => ''
                ];
            }
            $r['base_info'] = json_encode($r['base_info']);

            //房源标签
            $partern = '/<divclass="tagsclear"><divclass="name">([^<>]+)<\/div><divclass="content"><aclass="([^<>]+)"href="([^<>]+)">([^<>]+)<\/a><spanclass="([^<>]+)">([^<>]+)<\/span><\/div><\/div>/';
            $res = preg_match_all($partern,$html,$arr);
            if($res){
                $r['tags'] = $arr[3][0] ?? '';
            }else{
                $r['tags'] = '';
            }

            //户型分间
            $partern = '/<divclass="row"><divclass="col">([^<>]+)<\/div><divclass="col">([^<>]+)<\/div><divclass="col">([^<>]+)<\/div><divclass="col">([^<>]+)<\/div><\/div>/';
            $res = preg_match_all($partern,$html,$arr);
            if($res){
                $i = 0;
                $rows = [];
                while(isset($arr[0][$i])){
                    $rows[] = [
                        'name' => $arr[1][$i],
                        'area' => $arr[2][$i],
                        'orientation' => $arr[3][$i],
                        'window' => $arr[4][$i],
                    ];
                    $i++;
                }
                $r['house_division'] = json_encode($rows);
            }else{
                $r['house_division'] = '';
            }

            //特色
            $partern = '/<divclass="baseattributeclear"><divclass="name">([^<>]+)<\/div><divclass="content">([^<>]+)<\/div><\/div>/';
            $res = preg_match_all($partern,$html,$arr);
            if($res){
                $i = 0;
                while(isset($arr[1][$i])){
                    switch($arr[1][$i]){
                        case '核心卖点':
                            $r['feature']['sell_point'] = $arr[2][$i];
                            continue;
                        case '周边配套':
                            $r['feature']['around'] = $arr[2][$i];
                            continue;
                        case '交通出行':
                            $r['feature']['traffic'] = $arr[2][$i];
                            continue;
                        case '售房详情':
                            $r['feature']['sale'] = $arr[2][$i];
                            continue;
                        case '税费解析':
                            $r['feature']['tax'] = $arr[2][$i];
                            continue;
                        case '权属抵押':
                            $r['feature']['mortgage'] = $arr[2][$i];
                            continue;
                    }
                    $i++;
                }
                $r['feature'] = json_encode($r['feature']);
            }else{
                $r['feature'] = '';
            }

            //照片
            $partern = '/<divdata-index="([0-9]+)"><imgsrc="([^<>]+)"alt="([^<>]+)"><spanclass="name">([^<>]+)<\/span><\/div>/';
            $res = preg_match_all($partern,$html,$arr);
            if($res){
                $i = 0;
                while(isset($arr[0][$i])){
                    $r['pictures'][] = [
                        'desc' => $arr[3][$i],
                        'src' => $arr[2][$i],
                    ];
                    $i++;
                }
                $r['pictures'] = json_encode($r['pictures']);
            }else{
                $r['pictures'] = '';
            }

            //所在小区详情
            $partern = '/<spanclass="fl">([^<>]+)<\/span>/';
            $res = preg_match_all($partern,$html,$arr);
            if($res){
                $r['community']['desc'] = mb_substr($arr[1][0],0,mb_strlen($arr[1][0])-2);
                //小区均价
                $partern = '/<spanclass="xiaoqu_main_infoprice_red">([^<>]+)<\/span>/';
                $res = preg_match_all($partern,$html,$arr);
                if($res){
                    $r['community']['avg_price'] = $arr[1][0];
                }else{
                    $r['community']['avg_price'] = 0.00;
                }
                $partern = '/<divclass="xiaoqu_info"><labelclass="xiaoqu_main_label">([^<>]+)<\/label><spanclass="xiaoqu_main_info">([^<>]+)<\/span><\/div>/';
                $res = preg_match_all($partern,$html,$arr);
                if($res){
                    $i = 0;
                    while(isset($arr[0][$i])){
                        if($arr[1][$i] == '建筑年代'){
                            $r['community']['year_built'] = $arr[2][$i];
                        }
                        if($arr[1][$i] == '楼栋总数'){
                            $r['community']['total_building'] = $arr[2][$i];
                        }
                        $i++;
                    }
                }
                $partern = '/<divclass="xiaoqu_infoclear"><labelclass="xiaoqu_main_label">([^<>]+)<\/label><spanclass="xiaoqu_main_info">([^<>]+)<\/span><\/div>/';
                $res = preg_match_all($partern,$html,$arr);
                if($res){
                    $r['community']['building_type'] = $arr[2][0];
                }else{
                    $r['community']['building_type'] = '';
                }
                $r['community'] = json_encode($r['community']);
            }else{
                $r['community'] = '';
            }
            //匹配房源详情结束----------

            $r['created_at'] = date('Y-m-d H:i:s',time());
            $r['updated_at'] = date('Y-m-d H:i:s',time());
            $res = ShHous::insert($r);
            Log::info('插入数据执行情况 是否成功：' . $res . ' url: ' . $v . ' date: ' . date('Y-m-d H:i:s',time()));
            unset($r);
        }
        return ['code' => 0,'msg' => ''];
    }
}
