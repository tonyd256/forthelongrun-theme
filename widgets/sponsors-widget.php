<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

/**
 * Elementor Sponsors Widget.
 *
 * Elementor widget that inserts an embbedable content into the page, from any given URL.
 *
 * @since 1.0.0
 */
class Elementor_Sponsors_Widget extends \Elementor\Widget_Base {

  /**
   * Get widget name.
   *
   * Retrieve Sponsors widget name.
   *
   * @since 1.0.0
   * @access public
   * @return string Widget name.
   */
  public function get_name(): string {
    return 'sponsor';
  }

  /**
   * Get widget title.
   *
   * Retrieve Sponsors widget title.
   *
   * @since 1.0.0
   * @access public
   * @return string Widget title.
   */
  public function get_title(): string {
    return esc_html__( 'Sponsors', 'elementor-sponsors-widget' );
  }

  /**
   * Get widget icon.
   *
   * Retrieve Sponsors widget icon.
   *
   * @since 1.0.0
   * @access public
   * @return string Widget icon.
   */
  public function get_icon(): string {
    return 'eicon-code';
  }

  /**
   * Get widget categories.
   *
   * Retrieve the list of categories the Sponsors widget belongs to.
   *
   * @since 1.0.0
   * @access public
   * @return array Widget categories.
   */
  public function get_categories(): array {
    return [ 'general' ];
  }

  /**
   * Get widget keywords.
   *
   * Retrieve the list of keywords the Sponsors widget belongs to.
   *
   * @since 1.0.0
   * @access public
   * @return array Widget keywords.
   */
  public function get_keywords(): array {
    return [ 'sponsors', 'url', 'link' ];
  }

  /**
   * Get custom help URL.
   *
   * Retrieve a URL where the user can get more information about the widget.
   *
   * @since 1.0.0
   * @access public
   * @return string Widget help URL.
   */
  public function get_custom_help_url(): string {
    return 'https://developers.elementor.com/docs/widgets/';
  }

  /**
   * Whether the widget requires inner wrapper.
   *
   * Determine whether to optimize the DOM size.
   *
   * @since 1.0.0
   * @access public
   * @return bool Whether to optimize the DOM size.
   */
  public function has_widget_inner_wrapper(): bool {
    return false;
  }

  /**
   * Whether the element returns dynamic content.
   *
   * Determine whether to cache the element output or not.
   *
   * @since 1.0.0
   * @access protected
   * @return bool Whether to cache the element output.
   */
  protected function is_dynamic_content(): bool {
    return false;
  }

  /**
   * Register Sponsors widget controls.
   *
   * Add input fields to allow the user to customize the widget settings.
   *
   * @since 1.0.0
   * @access protected
   */
  protected function register_controls(): void {

    $this->start_controls_section(
      'content_section',
      [
        'label' => esc_html__( 'Content', 'elementor-sponsors-widget' ),
        'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
      ]
    );

    $this->add_control(
      'name',
      [
        'label' => esc_html__( 'Sponsor Name', 'elementor-sponsors-widget' ),
        'type' => \Elementor\Controls_Manager::TEXT,
      ]
    );

    $this->add_control(
      'image',
      [
        'label' => esc_html__( 'Choose Image', 'elementor-sponsors-widget' ),
        'type' => \Elementor\Controls_Manager::MEDIA,
        'media_types' => ['image'],
        'default' => [
          'url' => \Elementor\Utils::get_placeholder_image_src(),
        ],
      ]
    );

    $this->add_control(
      'description',
      [
        'label' => esc_html__( 'Description', 'elementor-sponsors-widget' ),
        'type' => \Elementor\Controls_Manager::WYSIWYG,
        'placeholder' => esc_html__( 'Type your description here', 'elementor-sponsors-widget' ),
      ]
    );

    $this->end_controls_section();

  }

  /**
   * Render Sponsors widget output on the frontend.
   *
   * Written in PHP and used to generate the final HTML.
   *
   * @since 1.0.0
   * @access protected
   */
  protected function render(): void {
    $settings = $this->get_settings_for_display();

    if ( empty( $settings['name'] ) || empty( $settings['image']['url'] ) ) {
      return;
    }

    echo '<div class="sponsors-elementor-widget">';

    // Get image 'thumbnail' by ID
    // echo wp_get_attachment_image( $settings['image']['id'], 'thumbnail' );

    // Get image HTML
    $this->add_render_attribute( 'image', 'src', $settings['image']['url'] );
    $this->add_render_attribute( 'image', 'alt', \Elementor\Control_Media::get_image_alt( $settings['image'] ) );
    $this->add_render_attribute( 'image', 'title', \Elementor\Control_Media::get_image_title( $settings['image'] ) );
    echo \Elementor\Group_Control_Image_Size::get_attachment_image_html( $settings, 'thumbnail', 'image' );

?>
      <h4 class="sponsors-elementor-widget-name"><?php echo $settings['name']; ?></h4>
      <?php echo $settings['description']; ?>
    </div>
<?php
  }
}
