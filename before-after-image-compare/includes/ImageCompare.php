<?php

class ImageCompare {

  public function __construct(){
    add_action( 'admin_menu', [$this, 'adminMenu'] );
		add_action( 'admin_enqueue_scripts', [$this, 'adminEnqueueScripts'] );
  }

  // Add admin menu page
			function adminMenu() {
				add_submenu_page(
					'tools.php', // Parent slug
					'Image Compare', // Page title
					'Image Compare', // Menu title
					'manage_options', // Capability
					'image-compare', // Menu slug
					[$this, 'renderPage'] // Callback function
				);
			}

						function adminEnqueueScripts($hook) {
				if( strpos( $hook, 'image-compare')){
					// wp_enqueue_style('view-css',ICB_DIR_URL . 'build/view.css',[],ICB_PLUGIN_VERSION);
					// wp_enqueue_script('view-js',ICB_DIR_URL . 'build/view.js',['react', 'react-dom'],ICB_PLUGIN_VERSION);
					wp_enqueue_style('icb-admin', ICB_DIR_URL . 'build/admin.css', [], ICB_PLUGIN_VERSION);
					wp_enqueue_script('icb-admin', ICB_DIR_URL . 'build/admin.js', [ 'react', 'react-dom' ], ICB_PLUGIN_VERSION);
				}
			}	
			
			function renderTemplate($content){
				$parseBlocks = parse_blocks($content);
				return render_block($parseBlocks[0]);
			}

      // Render the admin page content
			function renderPage() {
				$dashboardData = [
					"version" => ICB_PLUGIN_VERSION,
					"logo"	=> 'https://ps.w.org/before-after-image-compare/assets/icon-128x128.png?rev=3193735',
					"isPremium" => icbIsPremium()
				];

				?>
				<div id="icbAdminDashboard"  data-dashboard="<?php echo esc_attr( wp_json_encode( $dashboardData )  ); ?>">
					<div class='renderHere'></div>
					<div class="templates" style='display:none;'>
							<div class="default">
								<?php echo $this->renderTemplate('<!-- wp:icb/image-compare {"beforeImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg.jpg","alt":"","title":"","caption":""},"afterImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg2.jpg","alt":"","title":"","caption":""},"beforeLabel":"Before Image Label","afterLabel":"After Image Label","styles":{"container":{"height":{"desktop":"400px","tablet":"","mobile":""}},"dragg":{"line":{"bg":{"color":"#fff"},"size":2},"icon":{"color":""},"handler":{"color":""}}}} /-->'); ?>

								<?php echo $this->renderTemplate('<!-- wp:icb/image-compare {"beforeImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg.jpg","alt":"","title":"","caption":""},"afterImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg2.jpg","alt":"","title":"","caption":""},"orientation":"vertical","beforeLabel":"Before Image Label","afterLabel":"After Image Label","styles":{"container":{"height":{"desktop":"400px","tablet":"","mobile":""}},"dragg":{"line":{"bg":{"color":"#fff"},"size":2},"icon":{"color":""},"handler":{"color":""}}}} /-->'); ?>
							</div> 
							<div class="theme-1">
								<?php echo $this->renderTemplate('<!-- wp:icb/image-compare {"beforeImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg.jpg","alt":"","title":"","caption":""},"afterImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg2.jpg","alt":"","title":"","caption":""},"beforeLabel":"Before Image Label","afterLabel":"After Image Label","styles":{"container":{"height":{"desktop":"400px","tablet":"","mobile":""}},"dragg":{"line":{"bg":{"color":"#fff"},"size":2},"icon":{"color":""},"handler":{"color":""}}},"options":{"defaultOffset":0.5,"moveMouseOver":false,"isMoveClick":false,"autoSlide":{"isAutoSlide":true,"speed":5,"isHoverStop":true}}} /-->'); ?>
									<?php echo $this->renderTemplate('<!-- wp:icb/image-compare {"beforeImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg.jpg","alt":"","title":"","caption":""},"afterImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg2.jpg","alt":"","title":"","caption":""},"orientation":"vertical","beforeLabel":"Before Image Label","afterLabel":"After Image Label","styles":{"container":{"height":{"desktop":"400px","tablet":"","mobile":""}},"dragg":{"line":{"bg":{"color":"#fff"},"size":2},"icon":{"color":""},"handler":{"color":""}}},"options":{"defaultOffset":0.5,"moveMouseOver":false,"isMoveClick":false,"autoSlide":{"isAutoSlide":true,"speed":5,"isHoverStop":true}}} /-->');?>
								</div>
							<div class="theme-2" >
								<?php echo $this->renderTemplate('<!-- wp:icb/image-compare {"beforeImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg.jpg","alt":"","title":"","caption":""},"afterImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg2.jpg","alt":"","title":"","caption":""},"beforeLabel":"Before Image Label","afterLabel":"After Image Label","cap":"Caption of this Image","styles":{"container":{"height":{"desktop":"400px","tablet":"","mobile":""}},"dragg":{"line":{"bg":{"color":"#fff"},"size":2},"icon":{"color":""},"handler":{"color":""}}},"options":{"defaultOffset":0.5,"moveMouseOver":true,"isMoveClick":false,"autoSlide":{"isAutoSlide":false,"speed":5,"isHoverStop":true}}} /-->'); ?>
									<?php echo $this->renderTemplate('<!-- wp:icb/image-compare {"beforeImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg.jpg","alt":"","title":"","caption":""},"afterImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg2.jpg","alt":"","title":"","caption":""},"orientation":"vertical","beforeLabel":"Before Image Label","afterLabel":"After Image Label","cap":"Caption of this Image","styles":{"container":{"height":{"desktop":"400px","tablet":"","mobile":""}},"dragg":{"line":{"bg":{"color":"#fff"},"size":2},"icon":{"color":""},"handler":{"color":""}}},"options":{"defaultOffset":0.5,"moveMouseOver":true,"isMoveClick":false,"autoSlide":{"isAutoSlide":false,"speed":5,"isHoverStop":true}}} /-->');?>
							</div>
							<div class='theme-3'>
										<?php echo $this->renderTemplate('<!-- wp:icb/image-compare {"beforeImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg.jpg","alt":"","title":"","caption":""},"afterImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg2.jpg","alt":"","title":"","caption":""},"beforeLabel":"Before Image Label","afterLabel":"After Image Label","cap":"Caption of this Image","dragg":{"handleType":"rectangle-caret","shapeType":"default"},"styles":{"container":{"height":{"desktop":"400px","tablet":"","mobile":""}},"dragg":{"line":{"bg":{"color":"#fff"},"size":2},"icon":{"color":""},"handler":{"color":""}}},"options":{"defaultOffset":0.5,"moveMouseOver":false,"isMoveClick":true,"autoSlide":{"isAutoSlide":false,"speed":5,"isHoverStop":true}}} /-->');?>
											<?php echo $this->renderTemplate('<!-- wp:icb/image-compare {"beforeImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg.jpg","alt":"","title":"","caption":""},"afterImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg2.jpg","alt":"","title":"","caption":""},"orientation":"vertical","beforeLabel":"Before Image Label","afterLabel":"After Image Label","cap":"Caption of this Image","dragg":{"handleType":"rectangle-caret","shapeType":"default"},"styles":{"container":{"height":{"desktop":"400px","tablet":"","mobile":""}},"dragg":{"line":{"bg":{"color":"#fff"},"size":2},"icon":{"color":""},"handler":{"color":""}}},"options":{"defaultOffset":0.5,"moveMouseOver":false,"isMoveClick":true,"autoSlide":{"isAutoSlide":false,"speed":5,"isHoverStop":true}}} /-->');?>
							</div>
							<div class='theme-4'>
								<?php echo $this->renderTemplate('<!-- wp:icb/image-compare {"beforeImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg.jpg","alt":"","title":"","caption":""},"afterImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg2.jpg","alt":"","title":"","caption":""},"beforeLabel":"Before Image Label","afterLabel":"After Image Label","cap":"Caption of this Image","dragg":{"handleType":"capsule-stroke","shapeType":"default"},"styles":{"container":{"height":{"desktop":"400px","tablet":"","mobile":""}},"dragg":{"line":{"bg":{"color":"#fff"},"size":3},"icon":{"color":""},"handler":{"color":""}}},"options":{"defaultOffset":0.5,"moveMouseOver":false,"isMoveClick":true,"autoSlide":{"isAutoSlide":false,"speed":5,"isHoverStop":true}}} /-->');?>
									<?php echo $this->renderTemplate('<!-- wp:icb/image-compare {"beforeImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg.jpg","alt":"","title":"","caption":""},"afterImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg2.jpg","alt":"","title":"","caption":""},"orientation":"vertical","beforeLabel":"Before Image Label","afterLabel":"After Image Label","cap":"Caption of this Image","dragg":{"handleType":"capsule-fill","shapeType":"default"},"styles":{"container":{"height":{"desktop":"400px","tablet":"","mobile":""}},"dragg":{"line":{"bg":{"color":"#fff"},"size":3},"icon":{"color":""},"handler":{"color":""}}},"options":{"defaultOffset":0.5,"moveMouseOver":false,"isMoveClick":true,"autoSlide":{"isAutoSlide":false,"speed":5,"isHoverStop":true}}} /-->');?>
							</div>
							<div class="theme-5">
										<?php echo $this->renderTemplate('<!-- wp:icb/image-compare {"beforeImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg.jpg","alt":"","title":"","caption":""},"afterImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg2.jpg","alt":"","title":"","caption":""},"label":{"hAlign":"middle","vAlign":"center","isShowHover":false},"beforeLabel":"Before Image Label","afterLabel":"After Image Label","cap":"Caption of this Image","dragg":{"handleType":"circle-caret","shapeType":"default"},"styles":{"container":{"height":{"desktop":"400px","tablet":"","mobile":""}},"dragg":{"line":{"bg":{"color":"#fff"},"size":2},"icon":{"color":""},"handler":{"color":""}}},"options":{"defaultOffset":0.5,"moveMouseOver":false,"isMoveClick":true,"autoSlide":{"isAutoSlide":false,"speed":5,"isHoverStop":true}}} /-->');?>
										<?php echo $this->renderTemplate('<!-- wp:icb/image-compare {"beforeImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg.jpg","alt":"","title":"","caption":""},"afterImg":{"id":null,"url":"https://templates.bplugins.com/wp-content/uploads/2025/03/beafImg2.jpg","alt":"","title":"","caption":""},"beforeLabel":"Before Image Label","afterLabel":"After Image Label","cap":"Caption of this Image","dragg":{"handleType":"circle-big-caret","shapeType":"default"},"styles":{"container":{"height":{"desktop":"400px","tablet":"","mobile":""}},"dragg":{"line":{"bg":{"color":"#fff"},"size":3},"icon":{"color":""},"handler":{"color":""}}},"options":{"defaultOffset":0.5,"moveMouseOver":false,"isMoveClick":true,"autoSlide":{"isAutoSlide":false,"speed":5,"isHoverStop":true}}} /-->')?>
							</div>
						</div>
				</div>
				<?php
			}
}

new ImageCompare();