<?php

//曖昧なURLを推測し、補完する機能を停止
add_filter('do_redirect_guess_404_permalink', '__return_false');

//過去のURLを元にリダイレクトする機能を停止
remove_action('template_redirect', 'wp_old_slug_redirect');

//WPのバージョン情報非表示
remove_action('wp_head', 'wp_generator');

/*------------------------------------------
 *  wordpress テーマ 機能有効化
 *----------------------------------------*/
function my_setup()
{
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('title-tag');
    add_theme_support('html5', array('comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script'));
}
add_action('after_setup_theme', 'my_setup');

/*------------------------------------------
 *  CSSとJS、CDNの読み込み
 *----------------------------------------*/

function add_files()
{
    // CSS読込み

    // Common CSS
    wp_enqueue_style('common-style', get_template_directory_uri() . '/assets/css/common.css', array(), '1.0.0');
    // 個別 CSS

}
add_action('wp_enqueue_scripts', 'add_files');

/*------------------------------------------
 *  アーカイブページを有効にする
 *----------------------------------------*/
function post_has_archive($args, $post_type)
{
    if ('post' == $post_type) {
        $args['rewrite'] = true;
        $args['has_archive'] = 'archive';
        $args['label'] = '投稿';
    }
    return $args;
}
add_filter('register_post_type_args', 'post_has_archive', 10, 2);

/*------------------------------------------
 *  カテゴリーが未設定の場合、デフォルトカテゴリーを設定
 *----------------------------------------*/
function assign_default_category($post_id, $post, $update)
{
    // 既存の投稿の更新時は何もしない
    if ($update) {
        return;
    }

    // カテゴリーが割り当てられていない場合は、デフォルトカテゴリーを割り当てる
    $default_category = get_term_by('name', 'その他', 'category');
    if ($default_category && ! has_category('', $post_id)) {
        wp_set_post_categories($post_id, array($default_category->term_id));
    }
}
add_action('save_post', 'assign_default_category', 10, 3);

/*------------------------------------------
 *　コメント機能を無効化
 *----------------------------------------*/

// 管理画面の「コメント」メニューを削除
function remove_comments_menu()
{
    remove_menu_page('edit-comments.php');
}
add_action('admin_menu', 'remove_comments_menu');

// コメント関連の機能を無効化
function disable_comments_features()
{
    // 投稿編集画面でコメント機能を無効化
    remove_post_type_support('post', 'comments');
    remove_post_type_support('page', 'comments');

    // 管理バーから「コメント」を非表示に
    add_action('wp_before_admin_bar_render', function () {
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu('comments');
    });
}
add_action('init', 'disable_comments_features', 100);
