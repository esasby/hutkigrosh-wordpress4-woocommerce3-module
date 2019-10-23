<?php
/**
 * Created by IntelliJ IDEA.
 * User: nikit
 * Date: 23.10.2019
 * Time: 10:47
 */

namespace esas\hutkigrosh\controllers;


use esas\hutkigrosh\Registry;
use esas\hutkigrosh\wrappers\OrderWrapperWoo;
use WP_Post;
use WP_Query;

class ControllerNotifyWoo extends ControllerNotify
{
    public function getOrderWrapperForBill($billInfoRs)
    {


//        $args = array(
//            'post_type'		=>	'shop_order',
//            'meta_query'	=>	array(
//                array(
//                    OrderWrapperWoo::BILLID_METADATA_KEY	=>	$billInfoRs->getBillId()
//                )
//            )
//        );
//        $my_query = new WP_Query( $args );
//
//        if( $my_query->have_posts() ) {
//            $post = $my_query->the_post();
//            $order = Registry::getRegistry()->getOrderWrapper($post->get)
//        }
//        wp_reset_postdata();

        /** @var WP_Post[] $posts */
        $posts = get_posts( array(
            'meta_key'    => OrderWrapperWoo::BILLID_METADATA_KEY,
            'meta_value'  => $billInfoRs->getBillId(),
            'post_type'   => 'shop_order',
            'post_status' => 'any'
        ));
        $post = $posts[0];
        return Registry::getRegistry()->getOrderWrapper($post->ID);
    }

}