<?php
/**
 * PowerData Client Portal — mailer.php
 * All outgoing emails. Uses wp_mail() so the host's SMTP config applies.
 */

if ( ! defined( 'PD_PORTAL' ) ) { exit; }

// ── Send a magic-link login email ─────────────────────────────────────────
function pd_mail_magic_link( array $client ): bool {
    $link = pd_create_magic_link( (int) $client['id'] );
    $name = htmlspecialchars( $client['name'] );
    $mins = PD_MAGIC_LINK_LIFETIME / 60;

    $subject = 'Your sign-in link — ' . PD_SITE_NAME . ' Client Portal';

    $html = pd_email_wrapper( $name, <<<HTML
        <p style="margin:0 0 20px;font-size:16px;color:#46566E;line-height:1.6;">
            Click the button below to sign in to your secure file portal.
            This link expires in <strong>{$mins} minutes</strong> and can only be used once.
        </p>
        <p style="margin:0 0 28px;text-align:center;">
            <a href="{$link}" style="display:inline-block;background:#0E7A5A;color:#fff;font-weight:600;font-size:16px;padding:14px 32px;border-radius:999px;text-decoration:none;">
                Sign in to Portal →
            </a>
        </p>
        <p style="margin:0;font-size:13px;color:#6B7889;">
            If you did not request this link, you can safely ignore this email.
            Your password has not been changed.
        </p>
HTML );

    return pd_send_email( $client['email'], $subject, $html );
}

// ── Send expiry warning ───────────────────────────────────────────────────
function pd_mail_expiry_warning( array $client, array $files ): bool {
    $name  = htmlspecialchars( $client['name'] );
    $count = count( $files );
    $label = $count === 1 ? '1 file' : $count . ' files';

    $rows = '';
    foreach ( $files as $f ) {
        $fname   = htmlspecialchars( $f['original_name'] );
        $days    = pd_days_until_expiry( $f['expires_at'] );
        $expires = gmdate( 'M j, Y', strtotime( $f['expires_at'] ) );
        $rows   .= <<<ROW
            <tr>
                <td style="padding:10px 12px;border-bottom:1px solid #E7E2D9;font-size:14px;color:#0F1B2D;">{$fname}</td>
                <td style="padding:10px 12px;border-bottom:1px solid #E7E2D9;font-size:14px;color:#B5781A;white-space:nowrap;">{$expires} ({$days}d left)</td>
            </tr>
ROW;
    }

    $subject = "Action needed: {$label} expiring soon — " . PD_SITE_NAME;
    $portal_url = PD_PORTAL_URL;

    $html = pd_email_wrapper( $name, <<<HTML
        <p style="margin:0 0 16px;font-size:16px;color:#46566E;line-height:1.6;">
            The following {$label} shared with you will expire soon.
            Please download anything you need before the expiry date.
        </p>
        <table style="width:100%;border-collapse:collapse;margin:0 0 24px;">
            <thead>
                <tr style="background:#F4F1EA;">
                    <th style="padding:10px 12px;text-align:left;font-size:12px;letter-spacing:.1em;text-transform:uppercase;color:#6B7889;">File</th>
                    <th style="padding:10px 12px;text-align:left;font-size:12px;letter-spacing:.1em;text-transform:uppercase;color:#6B7889;">Expires</th>
                </tr>
            </thead>
            <tbody>{$rows}</tbody>
        </table>
        <p style="margin:0 0 24px;text-align:center;">
            <a href="{$portal_url}" style="display:inline-block;background:#0E7A5A;color:#fff;font-weight:600;font-size:16px;padding:14px 32px;border-radius:999px;text-decoration:none;">
                Go to your portal →
            </a>
        </p>
        <p style="margin:0;font-size:13px;color:#6B7889;">
            After expiry, files are kept securely on our end but will no longer
            be accessible to you through the portal.
        </p>
HTML );

    return pd_send_email( $client['email'], $subject, $html );
}

// ── Shared email HTML wrapper ─────────────────────────────────────────────
function pd_email_wrapper( string $client_name, string $body_html ): string {
    $site   = PD_SITE_NAME;
    $portal = PD_PORTAL_URL;
    $year   = gmdate( 'Y' );

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{$site}</title></head>
<body style="margin:0;padding:0;background:#FAF8F4;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#FAF8F4;padding:40px 16px;">
    <tr><td align="center">
      <table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;">

        <!-- Header -->
        <tr>
          <td style="background:#0F1B2D;border-radius:14px 14px 0 0;padding:24px 32px;">
            <p style="margin:0;font-family:Georgia,serif;font-weight:700;font-size:20px;color:#fff;letter-spacing:-.02em;">{$site}</p>
            <p style="margin:4px 0 0;font-size:12px;color:#9AA7B8;letter-spacing:.1em;text-transform:uppercase;">Client Portal</p>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="background:#fff;border-left:1px solid #E7E2D9;border-right:1px solid #E7E2D9;padding:32px;">
            <p style="margin:0 0 20px;font-size:14px;font-weight:600;color:#6B7889;letter-spacing:.08em;text-transform:uppercase;">Hello, {$client_name}</p>
            {$body_html}
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#F4F1EA;border:1px solid #E7E2D9;border-radius:0 0 14px 14px;padding:20px 32px;text-align:center;">
            <p style="margin:0;font-size:12px;color:#6B7889;">
              <a href="{$portal}" style="color:#0A5C43;font-weight:600;text-decoration:none;">{$site} Client Portal</a>
              &nbsp;·&nbsp; © {$year} {$site}. All rights reserved.
            </p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
}

// ── Core send function ────────────────────────────────────────────────────
function pd_send_email( string $to, string $subject, string $html ): bool {
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . PD_FROM_NAME . ' <' . PD_FROM_EMAIL . '>',
    ];
    return wp_mail( $to, $subject, $html, $headers );
}
