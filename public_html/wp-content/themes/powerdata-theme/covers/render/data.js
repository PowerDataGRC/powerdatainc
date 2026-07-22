/**
 * Category colorways, headlines, and motifs for the article cover system.
 * Single source of truth — template.js reads this to build each cover's HTML.
 * See ../../EDITORIAL.md / covers/DEVELOPERS.md for how to add a category.
 */

const CREAM = '#FAF8F4';
const INK = '#0F1B2D';

module.exports = [
  {
    slug: 'health-check',
    field: '#0E7A5A',
    bucket: 'dark',
    textHex: CREAM,
    serifTint: '#F6ECD7',
    supportTint: '#9FE1CB',
    decoDark: '#0A5C43',
    decoLight: '#16A276',
    headSans: 'Health,',
    headSerif: 'checked.',
    subhead: 'A clear read on where the business stands today.',
    motif: `<svg width="176" height="120" viewBox="0 0 88 60" fill="none">
  <path d="M6 34 H22 L29 18 L37 46 L45 22 L51 34 H58" stroke="#FAF8F4"
        stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M58 34 L65 41 L80 25" stroke="#FAF8F4" stroke-width="3.5"
        stroke-linecap="round" stroke-linejoin="round"/>
</svg>`,
  },
  {
    slug: 'data-protection',
    field: '#0F1B2D',
    bucket: 'dark',
    textHex: CREAM,
    serifTint: '#B5D4F4',
    supportTint: '#8FB0CC',
    decoDark: '#16314A',
    decoLight: '#22364A',
    headSans: 'Data,',
    headSerif: 'protected.',
    subhead: 'Simple habits that keep customer information where it belongs.',
    motif: `<svg width="184" height="140" viewBox="0 0 92 70" fill="none">
  <path d="M8 34 C 8 12, 84 12, 84 34" stroke="#FAF8F4" stroke-width="3.5" stroke-linecap="round"/>
  <path d="M46 8 L46 20" stroke="#B5D4F4" stroke-width="3.5" stroke-linecap="round"/>
  <rect x="20" y="40" width="16" height="20" rx="2.5" fill="#FAF8F4"/>
  <rect x="38" y="44" width="16" height="16" rx="2.5" fill="#B5D4F4"/>
  <rect x="56" y="40" width="16" height="20" rx="2.5" fill="#FAF8F4"/>
</svg>`,
  },
  {
    slug: 'cyber-security',
    field: '#fe5a1d',
    bucket: 'bright',
    textHex: INK,
    serifTint: CREAM,
    supportTint: '#7A2E12',
    decoDark: '#E8451A',
    decoLight: '#FF6E37',
    headSans: 'Cyber,',
    headSerif: 'Awareness.',
    subhead: 'Spotting the trick in the inbox before anyone clicks it.',
    motif: `<svg width="172" height="148" viewBox="0 0 86 74" fill="none">
  <path d="M62 6 L62 22 C62 33 49 33 49 23" stroke="#0F1B2D" stroke-width="3.5" stroke-linecap="round"/>
  <circle cx="62" cy="6" r="3.2" stroke="#0F1B2D" stroke-width="3"/>
  <rect x="10" y="32" width="58" height="38" rx="3" fill="#FAF8F4" stroke="#0F1B2D" stroke-width="3"/>
  <path d="M12 35 L39 55 L66 35" fill="none" stroke="#0F1B2D" stroke-width="3"
        stroke-linecap="round" stroke-linejoin="round"/>
</svg>`,
  },
  {
    slug: 'operational-efficiency',
    field: '#16697a',
    bucket: 'dark',
    textHex: CREAM,
    serifTint: '#A9DCE6',
    supportTint: '#8FC3D1',
    decoDark: '#0F4E5C',
    decoLight: '#1E7E92',
    headSans: 'Operations,',
    headSerif: 'streamlined.',
    subhead: 'Less friction between the work and the outcome.',
    motif: `<svg width="176" height="120" viewBox="0 0 88 60" fill="none">
  <path d="M8 46 C 22 46, 18 16, 36 28 C 48 36, 54 18, 74 20" stroke="#FAF8F4"
        stroke-width="3.5" stroke-linecap="round"/>
  <path d="M66 12 L78 20 L66 28" stroke="#FAF8F4" stroke-width="3.5"
        stroke-linecap="round" stroke-linejoin="round"/>
  <circle cx="8" cy="46" r="3.6" fill="#A9DCE6"/>
</svg>`,
  },
  {
    slug: 'compliance',
    field: '#5A2A54',
    bucket: 'dark',
    textHex: CREAM,
    serifTint: '#E7C4DD',
    supportTint: '#C79FBC',
    decoDark: '#47203F',
    decoLight: '#6E3768',
    headSans: 'Compliance,',
    headSerif: 'handled.',
    subhead: 'Meeting the mark — on paper and in practice.',
    motif: `<svg width="160" height="148" viewBox="0 0 80 74" fill="none">
  <circle cx="40" cy="28" r="20" fill="none" stroke="#FAF8F4" stroke-width="3.5"/>
  <path d="M31 28 L38 35 L50 22" stroke="#FAF8F4" stroke-width="3.5"
        stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M31 45 L26 68 L40 59 L54 68 L49 45" fill="#6E3768" stroke="#FAF8F4"
        stroke-width="2.6" stroke-linejoin="round"/>
</svg>`,
  },
];
