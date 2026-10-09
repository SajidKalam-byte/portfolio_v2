/**
 * Generates the portfolio's placeholder artwork. Run with `npm run images`.
 *
 *   public/images/projects/{slug}.svg   16:10 cover illustrations (1600×1000)
 *   public/images/og/{slug}.png         1200×630 Open Graph images
 *   public/images/og/default.png        site-wide Open Graph image
 *   public/favicon.svg, favicon.ico, apple-touch-icon.png, icon-192.png, icon-512.png
 *
 * All artwork is abstract: no screenshots, real data, or third-party imagery.
 * To use a real screenshot instead, upload it in the admin (or replace the file)
 * and point the project's image path at it.
 *
 * Output is committed to the repository, so production never needs Node.
 */
import { mkdirSync, writeFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import sharp from 'sharp';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const out = (path) => {
    const full = resolve(root, 'public', path);
    mkdirSync(dirname(full), { recursive: true });
    return full;
};

/* -------------------------------------------------------------------------- */
/* Palette & primitives                                                        */
/* -------------------------------------------------------------------------- */

const C = {
    bg: '#0e0e11',
    panel: '#16161a',
    panel2: '#1c1c21',
    panel3: '#232329',
    line: '#2b2b32',
    text: '#e7e7eb',
    muted: '#8b8b95',
    dim: '#4b4b55',
    faint: '#34343c',
    accent: '#e8651e',
    amber: '#ffb547',
    green: '#4ade80',
};

const MONO = "ui-monospace, 'SFMono-Regular', Menlo, Consolas, 'Liberation Mono', monospace";
const SANS = "'Segoe UI', 'Helvetica Neue', Arial, sans-serif";
const SERIF = "Georgia, 'Times New Roman', serif";

const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

const rect = (x, y, w, h, { fill = C.panel, stroke = C.line, r = 14, sw = 1.5, opacity = 1, dash = '' } = {}) =>
    `<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="${r}" fill="${fill}" stroke="${stroke}" stroke-width="${sw}" opacity="${opacity}"${dash ? ` stroke-dasharray="${dash}"` : ''}/>`;

const bar = (x, y, w, h = 12, fill = C.faint, r) => `<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="${r ?? h / 2}" fill="${fill}"/>`;

const text = (x, y, value, { size = 22, fill = C.muted, weight = 500, family = MONO, anchor = 'start', spacing = 0, style = 'normal' } = {}) =>
    `<text x="${x}" y="${y}" font-family="${family}" font-size="${size}" font-weight="${weight}" font-style="${style}" fill="${fill}" text-anchor="${anchor}" letter-spacing="${spacing}">${esc(value)}</text>`;

const circle = (cx, cy, r, fill, extra = '') => `<circle cx="${cx}" cy="${cy}" r="${r}" fill="${fill}" ${extra}/>`;

const line = (x1, y1, x2, y2, { stroke = C.dim, sw = 2, dash = '' } = {}) =>
    `<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" stroke="${stroke}" stroke-width="${sw}"${dash ? ` stroke-dasharray="${dash}"` : ''} stroke-linecap="round"/>`;

const arrow = (x1, y1, x2, y2, color = C.dim) =>
    `${line(x1, y1, x2, y2, { stroke: color, sw: 2.5, dash: '6 8' })}<path d="M${x2 - 12} ${y2 - 9} L${x2 + 2} ${y2} L${x2 - 12} ${y2 + 9}" fill="none" stroke="${color}" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>`;

const label = (x, y, value, color = C.muted) => text(x, y, value.toUpperCase(), { size: 17, fill: color, spacing: 2.5 });

/** Card with a mono heading. */
const card = (x, y, w, h, title, body = '', { accent = false } = {}) =>
    `<g>${rect(x, y, w, h, { fill: C.panel, stroke: accent ? C.accent : C.line })}${label(x + 28, y + 44, title, accent ? C.accent : C.muted)}${body}</g>`;

function canvas(width, height, body, { glow = [0.78, 0.12], grid = 56 } = {}) {
    return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${width} ${height}" width="${width}" height="${height}">
<defs>
  <pattern id="grid" width="${grid}" height="${grid}" patternUnits="userSpaceOnUse">
    <path d="M${grid} 0H0V${grid}" fill="none" stroke="#ffffff" stroke-opacity="0.045" stroke-width="1"/>
  </pattern>
  <radialGradient id="glow" cx="${glow[0]}" cy="${glow[1]}" r="0.6">
    <stop offset="0" stop-color="${C.accent}" stop-opacity="0.26"/>
    <stop offset="1" stop-color="${C.accent}" stop-opacity="0"/>
  </radialGradient>
  <linearGradient id="fade" x1="0" y1="0" x2="0" y2="1">
    <stop offset="0" stop-color="${C.bg}" stop-opacity="0"/>
    <stop offset="1" stop-color="${C.bg}" stop-opacity="0.9"/>
  </linearGradient>
  <linearGradient id="warm" x1="0" y1="0" x2="1" y2="1">
    <stop offset="0" stop-color="${C.accent}"/>
    <stop offset="1" stop-color="${C.amber}"/>
  </linearGradient>
</defs>
<rect width="${width}" height="${height}" fill="${C.bg}"/>
<rect width="${width}" height="${height}" fill="url(#grid)"/>
<rect width="${width}" height="${height}" fill="url(#glow)"/>
${body}
</svg>`;
}

/** App window chrome used by several covers. */
function appWindow(x, y, w, h, title) {
    return `${rect(x, y, w, h, { fill: C.panel, stroke: C.line, r: 22 })}
${`<path d="M${x} ${y + 64} H${x + w}" stroke="${C.line}" stroke-width="1.5"/>`}
${rect(x + 24, y + 20, 26, 26, { fill: C.accent, stroke: 'none', r: 8 })}
${text(x + 66, y + 41, title, { size: 20, fill: C.text, weight: 600 })}
${rect(x + w - 330, y + 16, 250, 34, { fill: C.panel2, stroke: C.line, r: 17 })}
${bar(x + w - 305, y + 28, 120, 10, C.faint)}
${circle(x + w - 46, y + 33, 15, C.panel3)}`;
}

/* -------------------------------------------------------------------------- */
/* Cover illustrations (1600 × 1000)                                           */
/* -------------------------------------------------------------------------- */

const W = 1600;
const H = 1000;

const covers = {
    'emr-patient-intake-platform': () => {
        const x = 110, y = 100, w = 1380, h = 800;
        const side = [0, 1, 2, 3, 4, 5].map((i) => {
            const active = i === 1;
            const yy = y + 110 + i * 64;
            return `${active ? rect(x + 18, yy - 22, 200, 46, { fill: 'rgba(232,101,30,0.12)', stroke: 'none', r: 12 }) : ''}
${rect(x + 36, yy - 9, 18, 18, { fill: active ? C.accent : C.faint, stroke: 'none', r: 5 })}${bar(x + 68, yy - 5, [96, 120, 84, 104, 90, 110][i], 10, active ? C.accent : C.faint)}`;
        }).join('');

        const steps = [0, 1, 2, 3].map((i) => `${circle(x + 300 + i * 118, y + 196, 16, i < 2 ? C.accent : C.panel3)}${i < 3 ? line(x + 318 + i * 118, y + 196, x + 400 + i * 118, y + 196, { stroke: i < 1 ? C.accent : C.faint, sw: 3 }) : ''}`).join('');

        const fields = [0, 1, 2, 3].map((i) => {
            const fx = x + 276 + (i % 2) * 250;
            const fy = y + 250 + Math.floor(i / 2) * 96;
            return `${bar(fx, fy, 90, 9, C.dim)}${rect(fx, fy + 20, 226, 44, { fill: C.panel2, stroke: C.line, r: 10 })}${bar(fx + 16, fy + 37, [120, 90, 140, 70][i], 10, C.faint)}`;
        }).join('');

        const calCells = [];
        for (let c = 0; c < 5; c++) {
            calCells.push(bar(x + 876 + c * 96, y + 150, 44, 9, C.dim));
            for (let r = 0; r < 4; r++) {
                calCells.push(rect(x + 866 + c * 96, y + 174 + r * 52, 84, 42, { fill: C.panel2, stroke: C.line, r: 8, sw: 1 }));
            }
        }
        const appts = [[0, 0, C.accent], [2, 1, C.amber], [1, 2, C.accent], [3, 0, C.faint], [4, 3, C.amber]].map(([c, r, col]) =>
            rect(x + 870 + c * 96, y + 178 + r * 52, 76, 34, { fill: col, stroke: 'none', r: 7, opacity: col === C.faint ? 1 : 0.85 })).join('');

        const soap = ['S', 'O', 'A', 'P'].map((l, i) => {
            const yy = y + 552 + i * 50;
            return `${circle(x + 300, yy, 17, i === 2 ? C.accent : C.panel3)}${text(x + 300, yy + 7, l, { size: 18, fill: i === 2 ? C.bg : C.text, weight: 700, anchor: 'middle' })}${bar(x + 332, yy - 6, [320, 260, 360, 220][i], 12, C.faint)}`;
        }).join('');

        const bill = [0, 1, 2, 3].map((i) => `${bar(x + 876, y + 548 + i * 40, 150, 10, C.faint)}${bar(x + 1050, y + 548 + i * 40, 66, 10, C.dim)}`).join('');

        const audit = [0, 1, 2].map((i) => `${circle(x + 1200, y + 552 + i * 50, 6, i === 0 ? C.green : C.dim)}${bar(x + 1218, y + 546 + i * 50, 110, 10, C.faint)}${bar(x + 1218, y + 562 + i * 50, 60, 8, C.panel3)}`).join('');

        return canvas(W, H, `
${appWindow(x, y, w, h, 'Clinic workspace')}
${side}
<path d="M${x + 236} ${y + 64} V${y + h}" stroke="${C.line}" stroke-width="1.5"/>
${card(x + 256, y + 90, 560, 380, 'Patient intake', steps + fields, { accent: true })}
${card(x + 846, y + 90, 510, 380, 'Schedule', calCells.join('') + appts)}
${card(x + 256, y + 490, 560, 290, 'SOAP note', soap)}
${card(x + 846, y + 490, 300, 290, 'Superbill', bill + bar(x + 876, y + 720, 240, 14, C.accent, 7))}
${card(x + 1166, y + 490, 190, 290, 'Audit log', audit)}
`);
    },

    'lanexis-customer-portal': () => {
        const x = 90, y = 110, w = 1060, h = 780;
        const kpis = [0, 1, 2].map((i) => `${rect(x + 30 + i * 336, y + 94, 312, 130, { fill: C.panel2, stroke: C.line })}${bar(x + 56 + i * 336, y + 124, 110, 10, C.dim)}${bar(x + 56 + i * 336, y + 156, [150, 120, 170][i], 28, i === 0 ? C.accent : C.panel3, 8)}${bar(x + 56 + i * 336, y + 198, 80, 8, C.faint)}`).join('');
        const chartPts = [0, 60, 40, 95, 80, 130, 115, 170, 150, 190].map((v, i) => `${x + 60 + i * 52},${y + 440 - v}`).join(' ');
        const chart = `${rect(x + 30, y + 248, 560, 250, { fill: C.panel2, stroke: C.line })}${label(x + 56, y + 290, 'Shipments')}
<polyline points="${chartPts}" fill="none" stroke="${C.accent}" stroke-width="4" stroke-linejoin="round" stroke-linecap="round"/>
${[0, 1, 2].map((i) => line(x + 56, y + 330 + i * 50, x + 564, y + 330 + i * 50, { stroke: C.line, sw: 1 })).join('')}`;
        const statuses = [C.green, C.amber, C.accent, C.green, C.amber];
        const table = `${rect(x + 30, y + 522, 1000, 228, { fill: C.panel2, stroke: C.line })}${label(x + 56, y + 564, 'Orders')}
${statuses.map((col, i) => `${bar(x + 56, y + 596 + i * 30, 180, 10, C.faint)}${bar(x + 300, y + 596 + i * 30, 120, 10, C.faint)}${rect(x + 880, y + 590 + i * 30, 100, 22, { fill: col, stroke: 'none', r: 11, opacity: 0.25 })}${bar(x + 896, y + 597 + i * 30, 68, 8, col)}`).join('')}`;
        const sheet = [];
        for (let r = 0; r < 5; r++) for (let c = 0; c < 4; c++) sheet.push(rect(x + 660 + c * 86, y + 318 + r * 30, 80, 24, { fill: r === 0 ? C.panel3 : C.panel, stroke: C.line, r: 3, sw: 1 }));
        const sync = `${rect(x + 620, y + 248, 410, 250, { fill: C.panel2, stroke: C.line })}${label(x + 646, y + 290, 'Google Sheets')}${sheet.join('')}
${rect(x + 820, y + 262, 190, 40, { fill: C.accent, stroke: 'none', r: 20 })}${text(x + 915, y + 288, 'Sync now', { size: 17, fill: C.bg, weight: 700, anchor: 'middle' })}`;

        const px = 1230, py = 170;
        const phone = `${rect(px, py, 290, 620, { fill: C.panel, stroke: C.line, r: 44, sw: 2 })}
${rect(px + 110, py + 18, 70, 12, { fill: C.panel3, stroke: 'none', r: 6 })}
${bar(px + 28, py + 70, 120, 14, C.text, 7)}${bar(px + 28, py + 96, 80, 10, C.dim)}
${[0, 1, 2, 3, 4].map((i) => `${rect(px + 22, py + 132 + i * 82, 246, 68, { fill: C.panel2, stroke: C.line, r: 14 })}${rect(px + 38, py + 150 + i * 82, 32, 32, { fill: i === 0 ? C.accent : C.panel3, stroke: 'none', r: 9 })}${bar(px + 84, py + 154 + i * 82, 130, 10, C.faint)}${bar(px + 84, py + 172 + i * 82, 80, 8, C.panel3)}`).join('')}
${line(px, py + 548, px + 290, py + 548, { stroke: C.line, sw: 1.5 })}
${[0, 1, 2, 3].map((i) => circle(px + 50 + i * 63, py + 584, 10, i === 0 ? C.accent : C.panel3)).join('')}`;

        return canvas(W, H, `
${appWindow(x, y, w, h, 'Customer portal')}
${kpis}${chart}${sync}${table}
${arrow(1156, 470, 1218, 470, C.accent)}
${phone}
`, { glow: [0.85, 0.2] });
    },

    'sms-spam-classification': () => {
        const msgs = [
            [140, 200, 300, true], [200, 300, 260, false], [140, 400, 320, true], [220, 500, 240, false], [140, 600, 280, false], [190, 700, 300, true],
        ].map(([mx, my, mw, spam]) => `${rect(mx, my, mw, 70, { fill: C.panel, stroke: spam ? C.accent : C.line, r: 20 })}${bar(mx + 22, my + 20, mw * 0.62, 10, C.faint)}${bar(mx + 22, my + 40, mw * 0.4, 10, C.panel3)}${text(mx + mw - 18, my + 44, spam ? 'spam' : 'ham', { size: 16, fill: spam ? C.accent : C.muted, anchor: 'end' })}`).join('');

        const cells = [];
        const seed = [3, 7, 1, 9, 4, 2, 8, 5, 6, 0];
        for (let r = 0; r < 9; r++) for (let c = 0; c < 7; c++) {
            const v = (seed[(r * 3 + c * 7) % 10] / 10) ** 1.6;
            cells.push(`<rect x="${690 + c * 42}" y="${250 + r * 42}" width="36" height="36" rx="6" fill="${C.accent}" fill-opacity="${(0.05 + v * 0.85).toFixed(2)}"/>`);
        }

        const pts = [];
        const rnd = (i) => ((Math.sin(i * 12.9898) * 43758.5453) % 1 + 1) % 1;
        for (let i = 0; i < 26; i++) pts.push(circle(1150 + rnd(i) * 150, 300 + rnd(i + 50) * 230, 9, C.dim));
        for (let i = 0; i < 18; i++) pts.push(circle(1300 + rnd(i + 100) * 140, 520 + rnd(i + 150) * 190, 9, C.accent));
        const plot = `${rect(1100, 230, 400, 540, { fill: C.panel, stroke: C.line, r: 20 })}${pts.join('')}
${line(1130, 380, 1470, 720, { stroke: C.text, sw: 3 })}
${line(1180, 350, 1480, 650, { stroke: C.muted, sw: 1.5, dash: '8 8' })}${line(1120, 440, 1420, 740, { stroke: C.muted, sw: 1.5, dash: '8 8' })}`;

        return canvas(W, H, `
${label(140, 170, 'Messages')}${msgs}
${arrow(560, 500, 650, 500)}
${label(690, 220, 'TF-IDF matrix')}${cells.join('')}
${arrow(1010, 500, 1080, 500)}
${label(1100, 200, 'SVM · max margin')}${plot}
${text(1300, 830, 'spam / ham', { size: 26, fill: C.text, weight: 600, anchor: 'middle' })}
`, { glow: [0.75, 0.3] });
    },

    'hate-speech-detection': () => {
        const chart = (ox, title, a, b) => `${rect(ox, 220, 360, 520, { fill: C.panel, stroke: C.line, r: 20 })}${label(ox + 30, 270, title)}
${line(ox + 40, 680, ox + 320, 680, { stroke: C.line, sw: 1.5 })}
${bar(ox + 80, 680 - a, 90, a, C.dim, 10)}${bar(ox + 200, 680 - b, 90, b, C.accent, 10)}
${text(ox + 125, 715, 'class 0', { size: 16, anchor: 'middle' })}${text(ox + 245, 715, 'class 1', { size: 16, anchor: 'middle' })}`;

        const curve = [];
        for (let i = 0; i <= 60; i++) {
            const t = (i / 60) * 12 - 6;
            const yv = 1 / (1 + Math.exp(-t));
            curve.push(`${1080 + i * 6.5},${690 - yv * 400}`);
        }
        const rnd = (i) => ((Math.sin(i * 78.233) * 12543.123) % 1 + 1) % 1;
        const dots = [];
        for (let i = 0; i < 14; i++) dots.push(circle(1090 + rnd(i) * 200, 690, 8, C.dim));
        for (let i = 0; i < 14; i++) dots.push(circle(1250 + rnd(i + 40) * 200, 290, 8, C.accent));

        return canvas(W, H, `
${chart(110, 'Before', 360, 70)}
${arrow(490, 480, 580, 480, C.accent)}
${text(535, 450, 'SMOTE', { size: 20, fill: C.accent, weight: 700, anchor: 'middle' })}
${chart(600, 'After', 360, 330)}
${rect(1040, 220, 450, 520, { fill: C.panel, stroke: C.line, r: 20 })}${label(1070, 270, 'Logistic regression')}
${line(1070, 490, 1470, 490, { stroke: C.line, sw: 1.5, dash: '6 8' })}
${dots.join('')}
<polyline points="${curve.join(' ')}" fill="none" stroke="${C.text}" stroke-width="4" stroke-linecap="round"/>
`, { glow: [0.5, 0.2] });
    },

    dailyexplainers: () => {
        const x = 120, y = 90, w = 1360, h = 820;
        const header = `${rect(x, y, w, h, { fill: C.panel, stroke: C.line, r: 22 })}
${text(x + 48, y + 70, 'Daily', { size: 34, fill: C.text, weight: 700, family: SANS })}${text(x + 136, y + 70, 'Explainers', { size: 34, fill: C.accent, weight: 400, family: SERIF, style: 'italic' })}
${[0, 1, 2, 3].map((i) => bar(x + 840 + i * 118, y + 52, 86, 12, C.faint)).join('')}
<path d="M${x} ${y + 110} H${x + w}" stroke="${C.line}" stroke-width="1.5"/>`;
        const hero = `${rect(x + 48, y + 150, 820, 360, { fill: 'url(#warm)', stroke: 'none', r: 18, opacity: 0.85 })}
<rect x="${x + 48}" y="${y + 150}" width="820" height="360" rx="18" fill="url(#fade)"/>
${rect(x + 80, y + 180, 120, 30, { fill: 'rgba(14,14,17,0.55)', stroke: 'none', r: 15 })}${bar(x + 100, y + 191, 80, 8, C.text)}
${bar(x + 80, y + 400, 620, 26, C.text, 8)}${bar(x + 80, y + 440, 460, 26, C.text, 8)}${bar(x + 80, y + 482, 200, 10, C.muted)}`;
        const cards = [0, 1, 2].map((i) => {
            const cx = x + 48 + i * 280;
            return `${rect(cx, y + 540, 260, 230, { fill: C.panel2, stroke: C.line, r: 16 })}${rect(cx + 18, y + 558, 224, 100, { fill: i === 1 ? C.accent : C.panel3, stroke: 'none', r: 10, opacity: i === 1 ? 0.6 : 1 })}${bar(cx + 18, y + 678, 200, 12, C.faint)}${bar(cx + 18, y + 700, 150, 12, C.faint)}${rect(cx + 18, y + 730, 64, 22, { fill: 'none', stroke: C.dim, r: 11 })}`;
        }).join('');
        const toc = `${rect(x + 908, y + 150, 404, 380, { fill: C.panel2, stroke: C.line, r: 16 })}${label(x + 936, y + 196, 'On this page')}
${[0, 1, 2, 3, 4, 5].map((i) => `${i === 2 ? bar(x + 936, y + 228 + i * 46, 4, 26, C.accent, 2) : bar(x + 936, y + 228 + i * 46, 2, 26, C.line, 1)}${bar(x + 956, y + 236 + i * 46, [220, 180, 240, 160, 200, 140][i], 10, i === 2 ? C.text : C.faint)}`).join('')}`;
        const tags = `${label(x + 908, y + 580, 'Tags · Series')}${['explainer', 'series', 'draft', 'scheduled'].map((t, i) => `${rect(x + 908 + (i % 2) * 190, y + 606 + Math.floor(i / 2) * 56, 170, 40, { fill: C.panel2, stroke: i === 3 ? C.accent : C.line, r: 20 })}${text(x + 993 + (i % 2) * 190, y + 632 + Math.floor(i / 2) * 56, t, { size: 16, fill: i === 3 ? C.accent : C.muted, anchor: 'middle' })}`).join('')}`;
        return canvas(W, H, `${header}${hero}${cards}${toc}${tags}`, { glow: [0.2, 0.1] });
    },

    'chess-for-two': () => {
        const bx = 150, by = 150, s = 88;
        const squares = [];
        for (let r = 0; r < 8; r++) for (let c = 0; c < 8; c++) {
            squares.push(`<rect x="${bx + c * s}" y="${by + r * s}" width="${s}" height="${s}" fill="${(r + c) % 2 ? C.panel2 : C.panel3}"/>`);
        }
        const piece = (c, r, light, kind) => {
            const cx = bx + c * s + s / 2;
            const cy = by + r * s + s / 2;
            const fill = light ? C.text : C.accent;
            if (kind === 'p') return circle(cx, cy + 6, 16, fill);
            if (kind === 'k') return `${rect(cx - 20, cy - 8, 40, 36, { fill, stroke: 'none', r: 8 })}${rect(cx - 4, cy - 30, 8, 22, { fill, stroke: 'none', r: 2 })}${rect(cx - 12, cy - 24, 24, 8, { fill, stroke: 'none', r: 2 })}`;
            if (kind === 'q') return `${rect(cx - 20, cy - 6, 40, 34, { fill, stroke: 'none', r: 8 })}${circle(cx, cy - 18, 12, fill)}`;
            if (kind === 'r') return `${rect(cx - 20, cy - 20, 40, 48, { fill, stroke: 'none', r: 6 })}`;
            return `${rect(cx - 18, cy - 4, 36, 32, { fill, stroke: 'none', r: 8 })}<path d="M${cx - 16} ${cy - 2} L${cx} ${cy - 30} L${cx + 16} ${cy - 2} Z" fill="${fill}"/>`;
        };
        const pieces = [
            [4, 7, true, 'k'], [3, 7, true, 'q'], [0, 7, true, 'r'], [5, 6, true, 'p'], [6, 6, true, 'p'], [4, 4, true, 'p'], [2, 5, true, 'b'],
            [4, 0, false, 'k'], [7, 0, false, 'r'], [3, 3, false, 'p'], [5, 1, false, 'p'], [6, 1, false, 'p'], [2, 2, false, 'b'], [3, 1, false, 'q'],
        ].map((p) => piece(...p)).join('');
        const hl = `${rect(bx + 4 * s + 4, by + 4 * s + 4, s - 8, s - 8, { fill: 'none', stroke: C.accent, r: 8, sw: 3 })}${rect(bx + 4 * s + 4, by + 6 * s + 4, s - 8, s - 8, { fill: 'none', stroke: C.accent, r: 8, sw: 2, dash: '6 6' })}`;

        const peer = (py, name, you) => `${rect(1040, py, 400, 150, { fill: C.panel, stroke: you ? C.accent : C.line, r: 20 })}${circle(1096, py + 75, 26, you ? C.accent : C.panel3)}${text(1140, py + 68, name, { size: 24, fill: C.text, weight: 600 })}${text(1140, py + 100, you ? 'host · peer id' : 'guest · via invite link', { size: 17 })}`;
        return canvas(W, H, `
${rect(bx - 16, by - 16, s * 8 + 32, s * 8 + 32, { fill: C.panel, stroke: C.line, r: 18 })}
${squares.join('')}${hl}${pieces}
${peer(210, 'Player A', true)}
${peer(640, 'Player B', false)}
${line(1240, 366, 1240, 634, { stroke: C.accent, sw: 3, dash: '10 10' })}
${rect(1255, 460, 200, 44, { fill: C.panel2, stroke: C.line, r: 22 })}${text(1355, 488, 'webrtc · data', { size: 16, fill: C.accent, anchor: 'middle' })}
`, { glow: [0.8, 0.5] });
    },

    'quiz-hub': () => {
        const x = 170, y = 150;
        const opts = ['A', 'B', 'C', 'D'].map((l, i) => {
            const sel = i === 2;
            const oy = y + 260 + i * 92;
            return `${rect(x + 50, oy, 700, 72, { fill: sel ? 'rgba(232,101,30,0.12)' : C.panel2, stroke: sel ? C.accent : C.line, r: 14 })}${circle(x + 94, oy + 36, 18, sel ? C.accent : C.panel3)}${text(x + 94, oy + 43, l, { size: 18, fill: sel ? C.bg : C.text, weight: 700, anchor: 'middle' })}${bar(x + 130, oy + 30, [340, 280, 380, 240][i], 12, sel ? C.text : C.faint)}`;
        }).join('');
        const quiz = `${rect(x, y, 800, 700, { fill: C.panel, stroke: C.line, r: 24 })}
${label(x + 50, y + 70, 'Question 3 of 10')}
${bar(x + 50, y + 94, 700, 8, C.panel3, 4)}${bar(x + 50, y + 94, 210, 8, C.accent, 4)}
${bar(x + 50, y + 150, 620, 22, C.text, 8)}${bar(x + 50, y + 186, 420, 22, C.text, 8)}
${opts}`;
        const chain = ['gemini', 'openai', 'rule-based'].map((t, i) => `${rect(1080, 430 + i * 120, 340, 80, { fill: C.panel, stroke: i === 0 ? C.accent : C.line, r: 18 })}${text(1250, 478 + i * 120, t, { size: 22, fill: i === 0 ? C.accent : C.muted, anchor: 'middle' })}${i < 2 ? arrow(1250, 512 + i * 120, 1250, 546 + i * 120) : ''}`).join('');
        return canvas(W, H, `${quiz}
${rect(1080, 250, 340, 90, { fill: C.accent, stroke: 'none', r: 45 })}${text(1250, 306, 'Generate quiz', { size: 26, fill: C.bg, weight: 700, anchor: 'middle', family: SANS })}
${label(1080, 400, 'Fallback chain')}${chain}
`, { glow: [0.8, 0.2] });
    },

    'rail-madad-ai-complaint-management': () => {
        const inputs = [
            ['text', `${bar(0, 0, 70, 10, C.text)}${bar(0, 20, 50, 10, C.faint)}`],
            ['image', `${rect(0, -8, 70, 46, { fill: C.panel3, stroke: 'none', r: 6 })}<path d="M4 34 L24 14 L40 28 L52 18 L68 34 Z" fill="${C.dim}"/>`],
            ['video', `${rect(0, -8, 70, 46, { fill: C.panel3, stroke: 'none', r: 6 })}<path d="M28 4 L48 15 L28 26 Z" fill="${C.text}"/>`],
        ].map(([name, icon], i) => `${rect(120, 220 + i * 200, 320, 140, { fill: C.panel, stroke: C.line, r: 20 })}<g transform="translate(160 ${282 + i * 200})">${icon}</g>${text(260, 300 + i * 200, name, { size: 24, fill: C.text, weight: 600 })}${arrow(452, 290 + i * 200, 560, 490)}`).join('');
        const ocr = `${rect(580, 410, 250, 160, { fill: C.panel, stroke: C.line, r: 20 })}${text(705, 480, 'OCR', { size: 34, fill: C.text, weight: 700, anchor: 'middle', family: SANS })}${text(705, 520, 'extract text', { size: 18, anchor: 'middle' })}`;
        const ai = `${arrow(840, 490, 920, 490, C.accent)}${rect(940, 380, 250, 220, { fill: 'rgba(232,101,30,0.1)', stroke: C.accent, r: 24 })}${text(1065, 478, 'AI', { size: 48, fill: C.accent, weight: 700, anchor: 'middle', family: SANS })}${text(1065, 520, 'multilingual', { size: 18, anchor: 'middle' })}${text(1065, 546, 'classification', { size: 18, anchor: 'middle' })}`;
        const cats = [0, 1, 2, 3].map((i) => `${arrow(1200, 490, 1270, 280 + i * 140)}${rect(1290, 240 + i * 140, 200, 80, { fill: C.panel, stroke: C.line, r: 16 })}${bar(1316, 272 + i * 140, [120, 90, 140, 100][i], 14, i === 0 ? C.accent : C.faint)}`).join('');
        return canvas(W, H, `${inputs}${ocr}${ai}${cats}
${text(800, 900, 'Concept design — SIH 2024 · PS 1711', { size: 20, anchor: 'middle' })}`, { glow: [0.65, 0.45] });
    },

    'private-client-work': () => {
        const rows = [];
        const widths = [[80, 260], [120, 180, 90], [120, 340], [160, 120], [80, 220, 140], [120, 300], [160, 200], [80, 160], [120, 260, 100], [80, 120]];
        widths.forEach((ws, r) => {
            let cx = 200;
            ws.forEach((wv, i) => {
                const redacted = (r + i) % 3 === 1;
                rows.push(bar(cx, 240 + r * 56, wv, 18, redacted ? C.panel3 : i === 0 ? C.accent : C.faint, 6));
                if (redacted) rows.push(`<rect x="${cx}" y="${240 + r * 56}" width="${wv}" height="18" rx="6" fill="none" stroke="${C.dim}" stroke-dasharray="4 5"/>`);
                cx += wv + 24;
            });
        });
        const lock = `${rect(1070, 360, 300, 280, { fill: C.panel, stroke: C.accent, r: 36, sw: 2.5 })}<path d="M1160 360 V310 a60 60 0 0 1 120 0 V360" fill="none" stroke="${C.accent}" stroke-width="16" stroke-linecap="round"/>${circle(1220, 480, 26, C.accent)}${rect(1210, 490, 20, 60, { fill: C.accent, stroke: 'none', r: 8 })}`;
        return canvas(W, H, `${rect(140, 170, 820, 660, { fill: C.panel, stroke: C.line, r: 24 })}${rows.join('')}${lock}
${text(1220, 700, 'confidential', { size: 26, fill: C.muted, anchor: 'middle' })}`, { glow: [0.75, 0.45] });
    },
};

/* -------------------------------------------------------------------------- */
/* Open Graph images (1200 × 630)                                              */
/* -------------------------------------------------------------------------- */

function wrap(value, max) {
    const words = value.split(/\s+/);
    const lines = [];
    let current = '';
    for (const word of words) {
        if ((current + ' ' + word).trim().length > max) {
            lines.push(current.trim());
            current = word;
        } else {
            current += ' ' + word;
        }
    }
    if (current.trim()) lines.push(current.trim());
    return lines;
}

function brand(x, y) {
    return `${rect(x, y, 52, 52, { fill: C.text, stroke: 'none', r: 14 })}${text(x + 12, y + 34, 'sk', { size: 22, fill: C.bg, weight: 700 })}${text(x + 38, y + 34, '_', { size: 22, fill: C.accent, weight: 700 })}
${text(x + 70, y + 34, 'Sajid Kalam', { size: 24, fill: C.text, weight: 600, family: SANS })}`;
}

function ogProject({ title, category, tech }) {
    const lines = wrap(title, 17).slice(0, 3);
    const titleSvg = lines.map((l, i) => text(70, 250 + i * 64, l, { size: 56, fill: C.text, weight: 700, family: SANS, spacing: -1 })).join('');
    let chipX = 70;
    const chips = tech.slice(0, 4).map((t) => {
        const w = t.length * 11 + 36;
        const svg = `${rect(chipX, 520, w, 44, { fill: C.panel, stroke: C.line, r: 10 })}${text(chipX + 18, 548, t, { size: 18, fill: C.muted })}`;
        chipX += w + 12;
        return svg;
    }).join('');
    return canvas(1200, 630, `
<rect x="760" y="0" width="440" height="630" fill="${C.panel}" opacity="0.35"/>
${brand(70, 64)}
${label(70, 180, category, C.accent)}
${titleSvg}
${text(70, 250 + lines.length * 64 + 8, 'Case study', { size: 24, fill: C.muted, family: SANS })}
${chips}
`, { glow: [0.9, 0.1], grid: 48 });
}

function ogDefault() {
    const nodes = ['Clients', 'Nginx', 'Gunicorn', 'Django + DRF', 'PostgreSQL'].map((n, i) => `${rect(820, 120 + i * 88, 290, 58, { fill: C.panel, stroke: i === 3 ? C.accent : C.line, r: 12 })}${text(844, 156 + i * 88, n, { size: 20, fill: i === 3 ? C.accent : C.text })}${i < 4 ? line(965, 178 + i * 88, 965, 208 + i * 88, { stroke: C.dim, sw: 2, dash: '4 6' }) : ''}`).join('');
    return canvas(1200, 630, `
${brand(70, 64)}
${text(70, 270, 'Backend thinking.', { size: 66, fill: C.text, weight: 700, family: SANS, spacing: -1.5 })}
${text(70, 350, 'Full-stack', { size: 66, fill: C.text, weight: 700, family: SANS, spacing: -1.5 })}
${text(405, 350, 'execution.', { size: 72, fill: C.accent, weight: 400, family: SERIF, style: 'italic' })}
${text(70, 430, 'Software Engineer · Python · Django · REST APIs · AWS', { size: 24, fill: C.muted, family: SANS })}
${text(70, 560, 'Siliguri, India', { size: 20, fill: C.dim })}
${nodes}
`, { glow: [0.85, 0.15], grid: 48 });
}

const ogProjects = {
    'emr-patient-intake-platform': { title: 'EMR & Patient Intake Platform', category: 'Healthcare software', tech: ['Django', 'DRF', 'JavaScript', 'AWS'] },
    'lanexis-customer-portal': { title: 'Lanexis Customer Portal', category: 'Business platform', tech: ['Django', 'React Native', 'PostgreSQL', 'AWS'] },
    'sms-spam-classification': { title: 'SMS Spam Classification', category: 'Machine learning', tech: ['Python', 'scikit-learn', 'SVM', 'TF-IDF'] },
    'hate-speech-detection': { title: 'Hate Speech Detection', category: 'Machine learning', tech: ['Python', 'scikit-learn', 'SMOTE'] },
    dailyexplainers: { title: 'DailyExplainers', category: 'Content platform', tech: ['Django', 'Tailwind CSS', 'Markdown'] },
    'chess-for-two': { title: 'Chess for Two', category: 'Web application', tech: ['JavaScript', 'PeerJS', 'WebRTC'] },
    'quiz-hub': { title: 'Quiz Hub — AI-Assisted Student Portal', category: 'Education', tech: ['Django', 'SQLite', 'Gemini API'] },
    'rail-madad-ai-complaint-management': { title: 'Rail Madad AI Complaint Management', category: 'Hackathon concept', tech: ['OCR', 'Deep learning', 'NLP'] },
    'private-client-work': { title: 'Private Client Systems', category: 'Client work', tech: ['Details on request'] },
};

/* -------------------------------------------------------------------------- */
/* Icons                                                                       */
/* -------------------------------------------------------------------------- */

const faviconSvg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
<rect width="64" height="64" rx="16" fill="#141416"/>
<text x="11" y="41" font-family="${MONO}" font-size="26" font-weight="700" fill="#ededef" letter-spacing="-1">sk</text>
<rect x="44" y="37" width="11" height="4" rx="2" fill="#e8651e"/>
</svg>`;

/** Minimal ICO container holding a single PNG image. */
function pngToIco(png, size) {
    const header = Buffer.alloc(6);
    header.writeUInt16LE(0, 0);
    header.writeUInt16LE(1, 2);
    header.writeUInt16LE(1, 4);
    const entry = Buffer.alloc(16);
    entry.writeUInt8(size >= 256 ? 0 : size, 0);
    entry.writeUInt8(size >= 256 ? 0 : size, 1);
    entry.writeUInt8(0, 2);
    entry.writeUInt8(0, 3);
    entry.writeUInt16LE(1, 4);
    entry.writeUInt16LE(32, 6);
    entry.writeUInt32LE(png.length, 8);
    entry.writeUInt32LE(22, 12);
    return Buffer.concat([header, entry, png]);
}

/* -------------------------------------------------------------------------- */
/* Build                                                                       */
/* -------------------------------------------------------------------------- */

async function main() {
    for (const [slug, draw] of Object.entries(covers)) {
        writeFileSync(out(`images/projects/${slug}.svg`), draw());
    }

    for (const [slug, meta] of Object.entries(ogProjects)) {
        const background = await sharp(Buffer.from(ogProject(meta))).png().toBuffer();
        const cover = await sharp(Buffer.from(covers[slug]())).resize(480, 300).png().toBuffer();
        const mask = Buffer.from(`<svg width="480" height="300"><rect width="480" height="300" rx="20" fill="#fff"/></svg>`);
        const rounded = await sharp(cover).composite([{ input: mask, blend: 'dest-in' }]).png().toBuffer();
        await sharp(background)
            .composite([{ input: rounded, left: 680, top: 165 }])
            .png({ compressionLevel: 9, palette: true, quality: 90 })
            .toFile(out(`images/og/${slug}.png`));
    }

    await sharp(Buffer.from(ogDefault())).png({ compressionLevel: 9, palette: true, quality: 90 }).toFile(out('images/og/default.png'));

    writeFileSync(out('favicon.svg'), faviconSvg);
    const icon = (size) => sharp(Buffer.from(faviconSvg)).resize(size, size).png().toBuffer();
    writeFileSync(out('favicon.ico'), pngToIco(await icon(32), 32));
    writeFileSync(out('apple-touch-icon.png'), await icon(180));
    writeFileSync(out('icon-192.png'), await icon(192));
    writeFileSync(out('icon-512.png'), await icon(512));

    console.log(`Generated ${Object.keys(covers).length} covers, ${Object.keys(ogProjects).length + 1} Open Graph images, and icons.`);
}

main().catch((error) => {
    console.error(error);
    process.exit(1);
});
