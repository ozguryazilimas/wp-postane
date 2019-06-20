<?php
/**
 * Class for displaying a list of posts without thumbnails
 *
 * In the table list are listed the posts without thumbnails. User can check specific posts and set the thumbnail for this
 *
 * @author Alexander Vitkalov <nechin.va@gmail.com>
 */

// Load the base class
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class APT_List_Table extends WP_List_Table {

	/**
	 * APT_List_Table constructor.
	 */
	function __construct() {
		//Set parent defaults
		parent::__construct(
			array(
				'singular' => 'post',     //singular name of the listed records
				'plural'   => 'posts',    //plural name of the listed records
				'ajax'     => false,      //does this table support ajax?
			)
		);
	}

	/**
	 * This method is called when the parent class can't find a method
	 * specifically build for a given column.
	 *
	 * @param array $item        A singular item (one full row's worth of data)
	 * @param string $column_name The name/slug of the column to be processed
	 *
	 * @return string Text or HTML to be placed inside the column <td>
	 */
	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'title':
			case 'author':
			case 'date':
				return $item[ $column_name ];
			default:
				return trim( $item[ $column_name ] ); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * @see WP_List_Table::::single_row_columns()
	 *
	 * @param array $item A singular item (one full row's worth of data)
	 *
	 * @return string Text to be placed inside the column <td> (movie title only)
	 */
	function column_title( $item ) {

		//Build row actions
		$actions = array(
			'edit' => sprintf( '<a href="%s">Edit</a>', get_edit_post_link( $item['ID'] ) ),
			//'delete' => sprintf( '<a href="?page=%s&action=%s&movie=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['ID'] ),
		);

		//Return the title contents
		return sprintf(
			'%1$s %2$s',
			/*$1%s*/
			$item['title'],
			/*$2%s*/
			$this->row_actions( $actions )
		);
	}

	/**
	 * @see WP_List_Table::::single_row_columns()
	 *
	 * @param array $item A singular item (one full row's worth of data)
	 *
	 * @return string Text to be placed inside the column <td> (movie title only)
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/
			$this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/
			$item['ID']                //The value of the checkbox should be the record's id
		);
	}

	/**
	 * This method dictates the table's columns and titles.
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
	 */
	function get_columns() {
		$columns = array(
			'cb'     => '<input type="checkbox" />', //Render a checkbox instead of text
			'title'  => __( 'Title', 'apt' ),
			'author' => __( 'Author', 'apt' ),
			'date'   => __( 'Date', 'apt' ),
		);

		return $columns;
	}

	/**
	 * This method merely defines which columns should be sortable and makes them clickable
	 *
	 * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'title' => array( 'title', false ),     //true means it's already sorted
			//'author'   => array( 'author', false ),
			'date'  => array( 'date', false ),
		);

		return $sortable_columns;
	}

	/**
	 * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
	 */
	function get_bulk_actions() {
		$actions = array(
			'generate' => __( 'Generate Thumbnail', 'apt' ),
		);

		return $actions;
	}

	/**
	 * Process bulk action
	 */
	function process_bulk_action() {
		// Detect when a bulk action is being triggered...
		if ( 'generate' === $this->current_action() ) {
			$this->generate_thumbnail();
		}
	}

	/**
	 * Generate thumbnail
	 */
	function generate_thumbnail() {
		if ( ! empty( $_GET['post'] ) ) {
			$ids = array();
			foreach ( $_GET['post'] as $post_id ) {
				$ids[] = $post_id;
			}
			$ids = implode( ',', $ids );

			$count = count( $_GET['post'] );
			?>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					$('.apt_loading').show();

					var rt_images = [<?php echo $ids; ?>];
					var generated = 0

					function genPostThumb(id) {
						$.post('admin-ajax.php', {
							action: 'generatepostthumbnail',
							id: id
						}, function(data) {
							if (data == true) {
								generated++;
							}

							if (rt_images.length) {
								genPostThumb(rt_images.shift());
							} else {
								document.location = '/wp-admin/options-general.php?page=generate-post-thumbnails&tab=custom&processed=' + generated;
							}
						});
					}

					genPostThumb(rt_images.shift());
				});
			</script>
			<?php
		}
	}

	/**
	 * Get our data
	 *
	 * @return array
	 */
	function get_data() {
		global $wpdb;

		$data = array();

		$query = auto_post_thumbnails()->get_posts_query();
		$posts = $wpdb->get_results( $query );

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$data[] = array(
					'ID'     => $post->ID,
					'title'  => $post->post_title,
					'author' => get_the_author_meta( 'display_name', $post->post_author ),
					'date'   => $post->post_date,
				);
			}
		}

		return $data;
	}

	/**
	 * Prepare data for display.
	 */
	function prepare_items() {
		// First, lets decide how many records per page to show
		$per_page = 10;

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();
		// Get id's of all the published posts for which post thumbnails does not exist.
		$data = $this->get_data();

		// This checks for sorting input and sorts the data in our array accordingly.
		function usort_reorder( $a, $b ) {
			$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
			$order   = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
			$result  = strcmp( $a[ $orderby ], $b[ $orderby ] ); //Determine sort order

			return ( 'asc' === $order ) ? $result : - $result; //Send final sort direction to usort
		}

		usort( $data, 'usort_reorder' );

		$current_page = $this->get_pagenum();

		$total_items = count( $data );

		$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->items = $data;

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,                  //WE have to calculate the total number of items
				'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
				'total_pages' => ceil( $total_items / $per_page ),   //WE have to calculate the total number of pages
			)
		);
	}

}

/**
 * Render page
 */
function apt_render_list_page() {
	// Create an instance of our package class...
	$apt_list_table = new APT_List_Table();
	// Fetch, prepare, sort, and filter our data...
	$apt_list_table->prepare_items();
	$is_message = isset( $_GET['processed'] ) ? true : false;
	?>

	<div id="message" class="updated fade"<?php if ( ! $is_message ) { ?> style="display:none"<?php } ?>>
		<?php echo $is_message ? '<p><strong>' . sprintf( esc_html__( 'All done! Success processed posts: %d', 'apt' ), $_GET['processed'] ) . '</strong></p>' : ''; ?>
	</div>

	<div class="wrap">

		<div class="apt_loading">
			<img class="apt-loading-image" src="<?php echo APT_PLUGIN_URL; ?>/img/ajax-loader.gif" alt="Loading..."/>
		</div>

		<form id="apt-posts-thumbnails" method="get">
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>"/>
			<input type="hidden" name="tab" value="custom"/>
			<!-- Now we can render the completed list table -->
			<?php $apt_list_table->display(); ?>
		</form>

	</div>
	<?php
}

apt_render_list_page();
