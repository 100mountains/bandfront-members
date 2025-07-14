<?php
/**
 * Audio Engine Settings Template for Bandfront Members
 * 
 * This template provides:
 * - Audio engine selection UI for global settings
 * - Integration with the state management system
 *
 * @package BandfrontMembers
 * @subpackage Views
 * @since 0.1
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register audio engine settings section
 */
add_action('bfm_module_audio_engine_settings', 'bfm_audio_engine_settings');

/**
 * Render audio engine global settings
 * 
 * @since 0.1
 * @param array $current_settings Current global settings array from state manager
 */
function bfm_audio_engine_settings($current_settings = []) {
    // Settings are already provided by the state manager
    $audio_engine = $current_settings['_bfp_audio_engine'] ?? 'mediaelement';
    $enable_visualizations = $current_settings['_bfp_enable_visualizations'] ?? 0;
    ?>
    <tr>
        <td colspan="2">
            <h3>🎵 <?php esc_html_e('Audio Engine Settings', 'bandfront-player'); ?></h3>
            <p class="description">
                <?php esc_html_e('Choose between MediaElement.js (traditional player) or WaveSurfer.js (modern waveform visualization).', 'bandfront-player'); ?>
            </p>
        </td>
    </tr>
    <tr>
        <td class="bfp-column-30">🎛️ <?php esc_html_e('Audio Engine', 'bandfront-player'); ?></td>
        <td>
            <label><input type="radio" name="_bfp_audio_engine" value="mediaelement" <?php checked($audio_engine, 'mediaelement'); ?>> <?php esc_html_e('MediaElement.js (Traditional)', 'bandfront-player'); ?></label><br>
            <label><input type="radio" name="_bfp_audio_engine" value="wavesurfer" <?php checked($audio_engine, 'wavesurfer'); ?>> <?php esc_html_e('WaveSurfer.js (Waveform)', 'bandfront-player'); ?></label>
        </td>
    </tr>
    <tr>
        <td class="bfp-column-30"><label for="_bfp_enable_visualizations">🌊 <?php esc_html_e('Enable Visualizations', 'bandfront-player'); ?></label></td>
        <td>
            <input type="checkbox" id="_bfp_enable_visualizations" name="_bfp_enable_visualizations" value="1" <?php checked($enable_visualizations, 1); ?>>
            <br><em class="bfp-em-text"><?php esc_html_e('Show waveform visualizations when using WaveSurfer.js engine', 'bandfront-player'); ?></em>
        </td>
    </tr>
    <?php
}