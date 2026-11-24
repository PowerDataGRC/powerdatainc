/**
 * The one fixed cover template. Every category's HTML is generated from
 * this function + its data.js entry — never hand-duplicated.
 *
 * The .thumb modifier (toggled at render time, not a separate template)
 * hides the kicker and subhead per §7 of the build brief, for the
 * headline-only 800×450 thumbnail crop.
 */

function renderCoverHTML(cat) {
	return `<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
  @font-face {
    font-family: 'Schibsted Grotesk';
    font-weight: 800;
    font-style: normal;
    src: url('../../assets/fonts/schibsted-grotesk-800.woff2') format('woff2');
  }
  @font-face {
    font-family: 'Instrument Serif';
    font-weight: 400;
    font-style: italic;
    src: url('../../assets/fonts/instrument-serif-italic-400.woff2') format('woff2');
  }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { margin: 0; }
  #cover.thumb .kicker-text,
  #cover.thumb .subhead { display: none; }
  #cover.thumb .motif { transform: scale(.72); transform-origin: top right; }
</style>
</head>
<body>

<div id="cover" style="width:1600px;height:900px;border-radius:0;overflow:hidden;
     background:${cat.field};position:relative;box-sizing:border-box;
     font-family:'Hanken Grotesk',system-ui,sans-serif;">

  <div style="position:absolute;right:-190px;bottom:-240px;width:800px;height:800px;
       border-radius:50%;background:${cat.decoDark};"></div>
  <div style="position:absolute;left:-110px;top:-140px;width:400px;height:400px;
       border-radius:50%;background:${cat.decoLight};"></div>

  <div style="position:absolute;inset:0;padding:72px 84px;display:flex;
       flex-direction:column;justify-content:space-between;">

    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <span class="kicker-text" style="font-size:22px;font-weight:600;letter-spacing:.18em;
            text-transform:uppercase;color:${cat.supportTint};">PowerData &middot; Field guide</span>
      <span class="motif">${cat.motif}</span>
    </div>

    <div>
      <h1 style="margin:0;font-family:'Schibsted Grotesk',sans-serif;font-weight:800;
          font-size:150px;line-height:.98;letter-spacing:-.03em;color:${cat.textHex};">
        ${cat.headSans}<br>
        <span style="font-family:'Instrument Serif',Georgia,serif;font-weight:400;
              font-style:italic;letter-spacing:0;color:${cat.serifTint};">${cat.headSerif}</span>
      </h1>
      <p class="subhead" style="margin:28px 0 0;font-size:34px;color:${cat.supportTint};max-width:36ch;">
        ${cat.subhead}</p>
    </div>

  </div>
</div>

</body>
</html>
`;
}

/**
 * Site-wide social-share card (1200×627, LinkedIn/Facebook 1.91:1) used as
 * og:image for any page that has neither a Featured Image nor a category
 * cover. Written to covers/og-default.html, so asset paths are relative to
 * covers/ (theme root is one level up).
 */
function renderDefaultCardHTML() {
	return `<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
  @font-face {
    font-family: 'Schibsted Grotesk';
    font-weight: 800;
    font-style: normal;
    src: url('../assets/fonts/schibsted-grotesk-800.woff2') format('woff2');
  }
  @font-face {
    font-family: 'Instrument Serif';
    font-weight: 400;
    font-style: italic;
    src: url('../assets/fonts/instrument-serif-italic-400.woff2') format('woff2');
  }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { margin: 0; }
</style>
</head>
<body>

<div id="cover" style="width:1200px;height:627px;overflow:hidden;background:#0F1B2D;
     position:relative;font-family:'Hanken Grotesk',system-ui,sans-serif;">

  <div style="position:absolute;right:-150px;bottom:-220px;width:620px;height:620px;
       border-radius:50%;background:#16314A;"></div>
  <div style="position:absolute;left:-90px;top:-120px;width:300px;height:300px;
       border-radius:50%;background:#22364A;"></div>

  <div style="position:absolute;inset:0;padding:64px 72px;display:flex;align-items:center;gap:56px;">
    <img src="../assets/pd-siteicon.png" width="300" height="300" alt=""
         style="flex:none;display:block;">
    <div>
      <h1 style="font-family:'Schibsted Grotesk',sans-serif;font-weight:800;font-size:104px;
          line-height:1;letter-spacing:-.03em;color:#FAF8F4;">PowerData</h1>
      <p style="margin-top:20px;font-family:'Instrument Serif',Georgia,serif;font-style:italic;
          font-size:44px;line-height:1.15;color:#B5D4F4;">Organize, protect, and run<br>your business.</p>
      <p style="margin-top:34px;font-size:22px;font-weight:600;letter-spacing:.16em;
          text-transform:uppercase;color:#8FB0CC;">powerdatainc.com</p>
    </div>
  </div>
</div>

</body>
</html>
`;
}

module.exports = { renderCoverHTML, renderDefaultCardHTML };
