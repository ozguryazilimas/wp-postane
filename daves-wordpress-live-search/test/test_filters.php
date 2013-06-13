add_filter('dwls_author_name', 'dwls_test_author_name');
function dwls_test_author_name($foo) {
	return 'Bob';
}

add_filter('dwls_post_date', 'dwls_test_post_date');
function dwls_test_post_date($foo) {
	return "Someday";
}

add_filter('dwls_the_excerpt', 'dwls_test_the_excerpt');
function dwls_test_the_excerpt($foo) {
	return 'This is the excerpt.';
}

add_filter('dwls_post_title', 'dwls_test_post_title');
function dwls_test_post_title($foo) {
	return 'the title';
}

add_filter('dwls_attachment_thumbnail', 'dwls_test_attachment_thumbnail');
function dwls_test_attachment_thumbnail($foo) {
	return 'http://csixty4.com/wp-content/uploads/2011/09/logo.png';
}
