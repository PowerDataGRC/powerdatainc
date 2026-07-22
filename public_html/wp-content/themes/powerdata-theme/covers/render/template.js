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

module.exports = { renderCoverHTML };
