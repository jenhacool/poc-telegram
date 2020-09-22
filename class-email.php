<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WC_POC_Telegram_Email extends WC_Email
{
	public function __construct()
	{
		$this->id = 'wc_poc_telegram';

		$this->customer_email = true;

		$this->title = 'POC Telegram Bot Key';

		$this->description = 'POC Telegram Bot Key';

		$this->heading = 'POC Telegram Bot Key';

		$this->subject = 'POC Telegram Bot Key';

		$this->template_html  = 'emails/template.php';

		$this->template_plain = 'emails/plain/plain.php';

		$this->template_base = plugin_dir_path( __FILE__ ) . 'templates/';

		add_action( 'woocommerce_order_status_completed', array( $this, 'trigger' ) );

		parent::__construct();

		$this->recipient = $this->get_option( 'recipient' );

		if ( ! $this->recipient ) {
			$this->recipient = get_option( 'admin_email' );
		}
	}

	public function trigger( $order_id )
	{
		if ( ! $order_id ) {
			return;
		}

		$order = new WC_Order( $order_id );

		if ( ! $order || get_post_meta( $order->get_id(), 'telegram_chatbot_code_sent', true ) == 1  ) {
		    return;
        }

		$this->object = $order;

		$key = $this->get_key( $order_id );

		if ( empty( $key ) ) {
		    $this->object->add_order_note(
		        __( 'Get Telegram chatbot code failed.', 'poc-telegram' )
            );
			return $this->send_email_to_email_when_failed( $order_id );
		}

		$this->setup_locale();

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		$this->recipient = $this->object->get_billing_email();

		$this->send(
			$this->get_recipient(),
			$this->get_subject(),
			$this->get_content(),
			$this->get_headers(),
			$this->get_attachments()
		);

        update_post_meta( $order_id, 'telegram_chatbot_code_sent', true );

		$this->object->add_order_note(
		    __( 'Telegram chatbot code sent to customer.', 'poc-foundation' )
        );

		$this->restore_locale();
	}

	public function get_content_html()
	{
		return wc_get_template_html( $this->template_html, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => false,
			'email'			=> $this
		), '', $this->template_base );
	}

	public function get_content_plain()
	{
		return wc_get_template_html( $this->template_plain, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => true,
			'email'			=> $this
		), '', $this->template_base );
	}

	public function init_form_fields() {

		$this->form_fields = array(
			'enabled'    => array(
				'title'   => 'Enable/Disable',
				'type'    => 'checkbox',
				'label'   => 'Enable this email notification',
				'default' => 'yes'
			),
			'recipient'  => array(
				'title'       => 'Recipient(s)',
				'type'        => 'text',
				'description' => sprintf( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', esc_attr( get_option( 'admin_email' ) ) ),
				'placeholder' => '',
				'default'     => ''
			),
			'subject'    => array(
				'title'       => 'Subject',
				'type'        => 'text',
				'description' => sprintf( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', $this->subject ),
				'placeholder' => '',
				'default'     => ''
			),
			'heading'    => array(
				'title'       => 'Email Heading',
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.' ), $this->heading ),
				'placeholder' => '',
				'default'     => ''
			),
			'email_type' => array(
				'title'       => 'Email type',
				'type'        => 'select',
				'description' => 'Choose which format of email to send.',
				'default'     => 'html',
				'class'       => 'email_type',
				'options'     => array(
					'plain'     => 'Plain text',
					'html'      => 'HTML', 'woocommerce',
					'multipart' => 'Multipart', 'woocommerce',
				)
			)
		);
	}

	protected function send_email_to_email_when_failed( $order_id )
	{
		$this->send(
			get_option( 'admin_email' ),
			'Get telegram key fail',
			"Can not get telegram key for order $order_id",
			$this->get_headers(),
			$this->get_attachments()
		);

		return;
	}

	protected function get_key( $order_id )
	{
		$key = get_post_meta( $order_id, 'telegram_chatbot_code', true );

		if ( ! empty( $key ) ) {
			return $key;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return '';
		}

		$api = new POC_Telegram_API();

        $code = $api->get_chatbot_code( array(
            'phoneNumber' => $order->get_billing_phone(),
            'fullName' => $order->get_billing_last_name() . ' ' . $order->get_billing_first_name(),
            'email' => $order->get_billing_email()
        ) );

        if ( empty( $code ) ) {
            return '';
        }

		update_post_meta( $order_id, 'telegram_chatbot_code', $code );

		return $code;
	}
}