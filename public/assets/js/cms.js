/* ==========================================================================
   Khagrachari District Bar Association: scripts.js
   One file for every page. Each page sets <body data-view="..."> and only
   the matching initializer runs.
   Sections: 1 Theme · 2 Data · 3 Helpers · 4 Components · 5 Pages · 6 Boot
   ========================================================================== */
(function () {
'use strict';

/* ==========================================================================
   1. THEME: Light / Dark / Auto (applied immediately to avoid a flash)
   ========================================================================== */
const mq = window.matchMedia('(prefers-color-scheme: dark)');
const THEME_ICONS = { light: 'bi-sun-fill', dark: 'bi-moon-stars-fill', auto: 'bi-circle-half' };
const savedMode = () => { try { return localStorage.getItem('theme') || 'auto'; } catch (e) { return 'auto'; } };

function applyTheme(mode) {
  document.documentElement.setAttribute('data-bs-theme', mode === 'auto' ? (mq.matches ? 'dark' : 'light') : mode);
  const icon = document.getElementById('themeIcon');
  if (icon) icon.className = 'bi ' + THEME_ICONS[mode];
  document.querySelectorAll('[data-theme]').forEach(b => b.classList.toggle('active', b.dataset.theme === mode));
}
applyTheme(savedMode());

function initTheme() {
  document.querySelectorAll('[data-theme]').forEach(b => b.addEventListener('click', () => {
    try { localStorage.setItem('theme', b.dataset.theme); } catch (e) { /* storage unavailable */ }
    applyTheme(b.dataset.theme);
  }));
  mq.addEventListener('change', () => { if (savedMode() === 'auto') applyTheme('auto'); });
  applyTheme(savedMode());
}

/* ==========================================================================
   2. DATA: sample content. Edit here to change the site.
   ========================================================================== */

/* Notices: [title, date, pdf path], newest first */
const notices=[
 ["Annual General Meeting 2026: notice and agenda","2026-11-12","notices/agm-2026.pdf"],
 ["Bar subscription renewal for the new year","2026-11-05","notices/subscription.pdf"],
 ["Court holiday schedule, December","2026-10-28","notices/holidays.pdf"],
 ["Legal aid clinic: volunteer advocates wanted","2026-10-20","notices/legal-aid.pdf"],
 ["New member enrolment: required documents","2026-10-11","notices/enrolment.pdf"],
 ["Election commission formed for executive committee","2026-10-04","notices/election-commission.pdf"],
 ["Voter list publication and objection period","2026-09-26","notices/voter-list.pdf"],
 ["Mandatory advocate ID card renewal","2026-09-15","notices/id-renewal.pdf"],
 ["Library hours and new book collection","2026-09-03","notices/library.pdf"],
 ["Condolence meeting for late senior advocate","2026-08-22","notices/condolence.pdf"],
 ["Seminar on land law reform: registration open","2026-08-10","notices/land-seminar.pdf"],
 ["Circular on court dress code and decorum","2026-07-29","notices/dress-code.pdf"],
 ["Nomination papers for executive committee election","2026-07-18","notices/nominations.pdf"],
 ["Special general meeting on Bar building renovation","2026-07-02","notices/renovation-sgm.pdf"],
 ["Continuing legal education workshop schedule","2026-06-21","notices/cle-workshop.pdf"],
 ["Eid holiday notice and office closure","2026-06-08","notices/eid-holiday.pdf"],
 ["Free medical camp for advocates and families","2026-05-24","notices/medical-camp.pdf"],
 ["Digitalization committee: member data update form","2026-05-09","notices/data-update.pdf"],
 ["Moot court competition for junior advocates","2026-04-17","notices/moot-court.pdf"],
 ["Audit report and accounts for the previous year","2026-03-30","notices/audit-report.pdf"]];

/* Events */
const events=[
 {title:"Seminar on Land Law Reform",type:"Seminar",date:"2026-11-21",time:"10:00 AM – 1:00 PM",venue:"Bar Association Hall",desc:"Experts review recent land-law amendments and what they mean for everyday practice in the hill district."},
 {title:"Legal Aid Awareness Program",type:"Community",date:"2026-11-28",time:"9:30 AM – 12:30 PM",venue:"Upazila Community Centre",desc:"Advocates meet villagers to explain free legal aid, how to apply and what documents to bring."},
 {title:"Continuing Legal Education Workshop",type:"Workshop",date:"2026-12-05",time:"3:00 PM – 6:00 PM",venue:"Conference Room, 2nd Floor",desc:"Hands-on training in drafting, pleadings and courtroom ethics for junior advocates."},
 {title:"Victory Day Discussion and Reception",type:"Program",date:"2026-12-16",time:"11:00 AM – 12:30 PM",venue:"Bar Association Hall",desc:"A commemorative discussion on law, liberation and constitutional values, followed by tea."},
 {title:"Annual Sports and Family Day",type:"Community",date:"2026-12-26",time:"8:00 AM – 4:00 PM",venue:"District Stadium Ground",desc:"A day of friendly sports, games and lunch for advocates and their families."},
 {title:"Digital Evidence and Cyber Law Seminar",type:"Seminar",date:"2027-01-09",time:"10:00 AM – 2:00 PM",venue:"Bar Association Hall",desc:"Practical guidance on presenting and challenging electronic evidence in court."},
 {title:"Moot Court Competition for Junior Advocates",type:"Workshop",date:"2026-10-17",time:"10:00 AM – 4:00 PM",venue:"Conference Room, 2nd Floor",desc:"Junior advocates argued a mock appeal before a bench of senior members."},
 {title:"Free Medical Camp for Advocates and Families",type:"Community",date:"2026-09-12",time:"9:00 AM – 1:00 PM",venue:"Bar Association Premises",desc:"General health check-ups, blood pressure and diabetes screening by volunteer doctors."},
 {title:"Orientation for Newly Enrolled Advocates",type:"Workshop",date:"2026-08-08",time:"2:00 PM – 5:00 PM",venue:"Conference Room, 2nd Floor",desc:"An introduction to Bar rules, chamber practice, court etiquette and mentoring."}];

/* Executive committees: first term is the present committee */
const roles=["President","Vice President","Vice President","General Secretary","Joint Secretary","Treasurer","Office Secretary","Library Secretary","Legal Aid Secretary","Cultural Secretary","Executive Member","Executive Member"];
const terms=[
 {id:"2026-2027",label:"2026–2027",present:true,names:["Md. Abdul Karim","Rehana Parvin","Kamal Hossain","Shahidul Islam","Tanvir Ahmed","Nasrin Akter","Mizanur Rahman","Sabbir Hossain","Joy Chakma","Farhana Yasmin","Mahmud Alam","Sharmin Sultana"]},
 {id:"2024-2025",label:"2024–2025",names:["Abdus Salam Khan","Nurul Haque","Sushil Tripura","Jahangir Alam","Ratan Chakma","Shirin Akter","Monir Hossain","Habibur Rahman","Anwara Begum","Pradip Chakma"]},
 {id:"2022-2023",label:"2022–2023",names:["Mohan Lal Chakma","Delwar Hossain","Salma Khatun","Faruk Ahmed","Biplob Tripura","Nasima Akter","Rafiqul Islam","Sanjib Chakma","Yasmin Ara","Alamgir Kabir"]},
 {id:"2020-2021",label:"2020–2021",names:["Anisur Rahman","Kalpana Chakma","Shamsul Alam","Moinul Hasan","Dipak Tripura","Rokeya Begum","Jashim Uddin","Tarek Mahmud","Mitu Akter"]},
 {id:"2018-2019",label:"2018–2019",names:["Golam Mostafa","Bimal Chakma","Aminul Islam","Sahidul Haque","Nirmal Tripura","Hosne Ara","Bashir Ahmed","Kabir Hossain","Rina Chakma"]},
 {id:"2016-2017",label:"2016–2017",names:["Abul Kalam","Sunil Kanti Chakma","Mosharraf Hossain","Nazrul Islam","Tapan Tripura","Shahana Parvin","Idris Ali","Kawsar Ahmed","Lipi Akter"]}
];

/* Founders and Digitalization committee: [name, designation] */
const founders = [["Late Adv. Abdus Salam","Founding President"],["Late Adv. Mohan Chakma","Founding Secretary"],["Late Adv. Nurul Haque","Co-founder"],["Adv. Sushil Tripura","Founding Member"],["Late Adv. Jahanara Begum","Founding Member"],["Adv. Ratan Chakma","Early Organizer"]];
const digital = [["Tanvir Ahmed","Convener"],["Sabbir Hossain","Member Secretary"],["Nusrat Jahan","IT Coordinator"],["Joy Chakma","Member"],["Mahmud Alam","Member"],["Sharmin Sultana","Member"]];

/* Articles and authors */
const authors={"rehana": {"name": "Adv. Rehana Parvin", "role": "Vice President and Advocate, Khagrachari District Bar Association", "bio": "Rehana Parvin practises criminal and family law and mentors junior advocates through the Bar’s training programme."}, "tanvir": {"name": "Adv. Tanvir Ahmed", "role": "Joint Secretary and Advocate, Khagrachari District Bar Association", "bio": "Tanvir Ahmed practises commercial and civil law and convenes the Association’s digitalization committee."}, "joy": {"name": "Adv. Joy Chakma", "role": "Legal Aid Secretary and Advocate, Khagrachari District Bar Association", "bio": "Joy Chakma focuses on land and customary-law matters and coordinates the Bar’s legal aid clinics."}, "karim": {"name": "Adv. Md. Abdul Karim", "role": "President, Khagrachari District Bar Association", "bio": "Md. Abdul Karim has practised before the district courts for decades and leads the Association’s work on professional ethics."}, "nasrin": {"name": "Adv. Nasrin Akter", "role": "Treasurer and Advocate, Khagrachari District Bar Association", "bio": "Nasrin Akter works on women’s and children’s rights and consumer matters and volunteers in the legal aid clinic."}, "kamal": {"name": "Adv. Kamal Hossain", "role": "Vice President and Advocate, Khagrachari District Bar Association", "bio": "Kamal Hossain practises civil litigation and mediation and trains advocates in courtroom skills."}};
const articles=[
{
"id": 1,
"title": "Understanding bail jurisprudence in practice",
"cat": "Criminal Law",
"author": "rehana",
"date": "2026-10-24",
"icon": "bi-journal-text",
"excerpt": "Bail sits at the meeting point of two principles: the presumption of innocence and the court’s duty to protect society. For many accused persons in our district, the first hearing decides how the next months of their life will look.",
"body": "<p class=\"lead-p\">Bail sits at the meeting point of two principles: the presumption of innocence and the court’s duty to protect society. For many accused persons in our district, the first hearing decides how the next months of their life will look.</p><h2>What the court weighs</h2><p>Courts generally consider the seriousness of the allegation, the material placed before them, the risk that the accused may flee or tamper with evidence, and the time already spent in custody. A well-prepared application speaks to each of these points directly rather than relying on general pleas.</p><blockquote>A bail hearing is short, but a good application is built long before the hearing begins.</blockquote><h3>Practical preparation</h3><p>Collect the accused’s residence and family details and documents showing ties to the community. Present a clear, honest summary of the facts, and be ready to propose reasonable conditions.</p>"
},
{
"id": 2,
"title": "Digital evidence and the courtroom",
"cat": "Evidence & Technology",
"author": "tanvir",
"date": "2026-10-09",
"icon": "bi-laptop",
"excerpt": "Phone recordings, messages, photographs and call records now appear in ordinary cases, not only specialist ones. Advocates on both sides need to understand how such material is collected, preserved and proved.",
"body": "<p class=\"lead-p\">Phone recordings, messages, photographs and call records now appear in ordinary cases, not only specialist ones. Advocates on both sides need to understand how such material is collected, preserved and proved.</p><h2>Authenticity comes first</h2><p>Before the content is argued about, the court must be satisfied that the material is genuine and unaltered. Questions about source, custody, the device used and any copies made are therefore central.</p><blockquote>The strength of electronic evidence lies less in what it shows and more in how it was handled.</blockquote><h3>Tips for the chamber</h3><p>Record who obtained the material and when, keep the original device or file safe, and avoid editing or forwarding it unnecessarily. When challenging such evidence, examine the chain of custody carefully.</p>"
},
{
"id": 3,
"title": "Land disputes in hill districts: a practical guide",
"cat": "Land Law",
"author": "joy",
"date": "2026-09-27",
"icon": "bi-map",
"excerpt": "Few matters occupy hill-district advocates more than questions of land, where records, possession and long-standing community practice do not always line up neatly.",
"body": "<p class=\"lead-p\">Few matters occupy hill-district advocates more than questions of land, where records, possession and long-standing community practice do not always line up neatly.</p><h2>Start with the documents</h2><p>Collect every available record: deeds, mutation papers, survey records, rent receipts and tax payments. Note any gaps and any differences between names, plot numbers or areas across documents.</p><blockquote>In land cases, the paper trail is the case.</blockquote><h3>Consider the wider context</h3><p>Ask how the land has actually been used and by whom, and whether customary arrangements or local authorities hold relevant information. Early, honest advice about the strengths and gaps in a client’s papers saves time and cost for everyone.</p>"
},
{
"id": 4,
"title": "Legal aid: how citizens can access free representation",
"cat": "Legal Aid",
"author": "nasrin",
"date": "2026-09-14",
"icon": "bi-people",
"excerpt": "Justice should not depend on a person’s ability to pay. Legal aid exists so that people with limited means can still be heard, advised and represented.",
"body": "<p class=\"lead-p\">Justice should not depend on a person’s ability to pay. Legal aid exists so that people with limited means can still be heard, advised and represented.</p><h2>Who can ask for help</h2><p>Eligibility and procedures are set by the relevant legal aid authorities and are generally directed at people who cannot afford a lawyer. Citizens can ask the District Legal Aid Committee or approach our Bar Association’s legal aid committee for guidance on where to start.</p><blockquote>Access to a lawyer is the first step toward access to justice.</blockquote><h3>How advocates can help</h3><p>Volunteer for the clinic roster, explain the process in plain language, and help applicants gather their documents. Even a single hour of clear advice can change the course of a person’s case.</p>"
},
{
"id": 5,
"title": "Mediation and alternative dispute resolution",
"cat": "Civil Law",
"author": "kamal",
"date": "2026-09-01",
"icon": "bi-chat-left-text",
"excerpt": "Not every dispute needs a full trial. Mediation and other forms of alternative dispute resolution can settle matters faster, at lower cost and with less damage to relationships.",
"body": "<p class=\"lead-p\">Not every dispute needs a full trial. Mediation and other forms of alternative dispute resolution can settle matters faster, at lower cost and with less damage to relationships.</p><h2>Where it works best</h2><p>Family property questions, neighbour and boundary disagreements, and many commercial claims are often well suited to a negotiated settlement, particularly where the parties must continue to live or work alongside each other.</p><blockquote>A settlement both sides can live with is often worth more than a judgment one side must endure.</blockquote><h3>The advocate’s role</h3><p>Explain the options candidly, prepare the client for realistic outcomes, and make sure any agreement is clearly written and properly recorded so that it can be relied on later.</p>"
},
{
"id": 6,
"title": "Professional ethics for the young advocate",
"cat": "Professional Ethics",
"author": "karim",
"date": "2026-08-19",
"icon": "bi-shield-check",
"excerpt": "A young advocate’s reputation begins long before the first big case. It is built in small, daily choices about honesty, preparation and respect.",
"body": "<p class=\"lead-p\">A young advocate’s reputation begins long before the first big case. It is built in small, daily choices about honesty, preparation and respect.</p><h2>Core duties</h2><p>Be truthful with the court, loyal to your client within the law, courteous to colleagues and careful with client money and confidences. These duties are not obstacles to good advocacy; they are what make it trustworthy.</p><blockquote>Your name is the only chamber you carry everywhere.</blockquote><h3>Habits worth building</h3><p>Prepare every file fully, never overpromise results, respect court time, and ask a senior colleague when in doubt. The Bar’s mentoring programme exists precisely for this.</p>"
},
{
"id": 7,
"title": "Writing a clear plaint: common drafting mistakes",
"cat": "Legal Drafting",
"author": "rehana",
"date": "2026-08-05",
"icon": "bi-pencil-square",
"excerpt": "A plaint is often the first document a judge reads about a client’s case. A clear one sets the tone; a muddled one is difficult to repair.",
"body": "<p class=\"lead-p\">A plaint is often the first document a judge reads about a client’s case. A clear one sets the tone; a muddled one is difficult to repair.</p><h2>Common mistakes</h2><p>Typical problems include mixing facts with arguments, leaving out dates or descriptions of property, unclear relief sought, and attaching documents that are not referred to anywhere in the text.</p><blockquote>Plead facts, not feelings, and say plainly what you want the court to do.</blockquote><h3>A simple checklist</h3><p>State the parties and their addresses, set out facts in numbered paragraphs in order, identify the cause of action and the court’s jurisdiction, and list the relief sought precisely.</p>"
},
{
"id": 8,
"title": "Protecting women and children: the role of the local Bar",
"cat": "Human Rights",
"author": "nasrin",
"date": "2026-07-22",
"icon": "bi-heart",
"excerpt": "Local Bars are well placed to support women and children who face violence or exploitation, because advocates see these problems first-hand and know the local institutions.",
"body": "<p class=\"lead-p\">Local Bars are well placed to support women and children who face violence or exploitation, because advocates see these problems first-hand and know the local institutions.</p><h2>Practical support</h2><p>Advocates can explain available legal protections, help victims reach the right authorities and support services, and ensure that their dignity and privacy are respected during proceedings.</p><blockquote>Protection begins with someone willing to listen and to explain the options clearly.</blockquote><h3>What our Bar can do</h3><p>Maintain a list of volunteer advocates, build links with local support organisations, and organise awareness sessions in schools and communities across the district.</p>"
},
{
"id": 9,
"title": "Cheque dishonour cases: what every litigant should know",
"cat": "Commercial Law",
"author": "tanvir",
"date": "2026-07-08",
"icon": "bi-cash-coin",
"excerpt": "A dishonoured cheque can quickly turn a business arrangement into a legal dispute, and strict time limits often apply.",
"body": "<p class=\"lead-p\">A dishonoured cheque can quickly turn a business arrangement into a legal dispute, and strict time limits often apply.</p><h2>Act promptly</h2><p>The law on dishonoured cheques sets out steps and deadlines for notices and complaints. Missing a step can weaken an otherwise sound case, so people should seek advice as soon as the cheque is returned.</p><blockquote>In cheque matters, timing is often the difference between a strong case and a lost one.</blockquote><h3>Keep these documents</h3><p>Preserve the original cheque, the bank’s return memo, the written demand notice with proof of delivery, and any records of the underlying transaction.</p>"
},
{
"id": 10,
"title": "Customary law and statutory law in the hill districts",
"cat": "Land Law",
"author": "joy",
"date": "2026-06-25",
"icon": "bi-book",
"excerpt": "In the hill districts, statutory law and customary practice exist side by side. Understanding where they meet is part of everyday advocacy.",
"body": "<p class=\"lead-p\">In the hill districts, statutory law and customary practice exist side by side. Understanding where they meet is part of everyday advocacy.</p><h2>Respecting both systems</h2><p>Customary institutions play an important role in community life, while courts apply the law of the land. Good practice means listening carefully to how a community understands its own arrangements while advising clients about their rights under the statutes.</p><blockquote>Justice is strongest when people recognise it as theirs.</blockquote><h3>Working together</h3><p>Advocates can build trust by explaining court procedures to community leaders, learning local practices respectfully, and helping clients present customary evidence clearly when it is relevant.</p>"
},
{
"id": 11,
"title": "Preparing a witness for cross-examination",
"cat": "Court Practice",
"author": "kamal",
"date": "2026-06-11",
"icon": "bi-mic",
"excerpt": "Many cases turn on how a witness holds up under questioning. Preparing a witness is not about changing their account; it is about helping them tell it clearly.",
"body": "<p class=\"lead-p\">Many cases turn on how a witness holds up under questioning. Preparing a witness is not about changing their account; it is about helping them tell it clearly.</p><h2>Explain the process</h2><p>Walk the witness through the courtroom, who will ask questions and in what order. Many witnesses are simply afraid of the unknown, and familiarity reduces that fear.</p><blockquote>The best preparation teaches a witness to listen carefully and answer only what is asked.</blockquote><h3>Ethical limits</h3><p>Never coach a witness to say something untrue. Encourage honesty, calm answers and a willingness to say “I do not remember” when that is the truth.</p>"
},
{
"id": 12,
"title": "Going digital: what e-filing could mean for district courts",
"cat": "Court Reform",
"author": "tanvir",
"date": "2026-05-28",
"icon": "bi-cloud-upload",
"excerpt": "Digital filing promises faster access, fewer lost papers and less travel for litigants in remote areas, but it needs careful planning to work well.",
"body": "<p class=\"lead-p\">Digital filing promises faster access, fewer lost papers and less travel for litigants in remote areas, but it needs careful planning to work well.</p><h2>What districts need</h2><p>Reliable connectivity, trained staff, clear rules on electronic signatures and records, and training for advocates are all part of a realistic transition.</p><blockquote>Technology should make the court easier to reach, not harder.</blockquote><h3>How the Bar can help</h3><p>Our digitalization committee is working on member records and notices as a first step, and will share what it learns with others in the profession.</p>"
},
{
"id": 13,
"title": "Consumer rights: filing a complaint step by step",
"cat": "Consumer Law",
"author": "nasrin",
"date": "2026-05-14",
"icon": "bi-bag-check",
"excerpt": "Consumers often hesitate to complain because the process seems complicated. A clear, step-by-step approach can make it much more manageable.",
"body": "<p class=\"lead-p\">Consumers often hesitate to complain because the process seems complicated. A clear, step-by-step approach can make it much more manageable.</p><h2>Before filing</h2><p>Collect receipts, warranties, messages and photographs, and first write to the seller or service provider describing the problem and the remedy you want. Keep copies of everything.</p><blockquote>A well-documented complaint is half the remedy.</blockquote><h3>Where to go next</h3><p>If the seller does not respond, the consumer protection law provides complaint routes to the relevant authorities. An advocate can help identify the correct forum and prepare the complaint.</p>"
}
];

/* ==========================================================================
   3. HELPERS
   ========================================================================== */
const $ = id => document.getElementById(id);
const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const parseDate = d => new Date(d + 'T00:00:00');
const fmtLong = d => parseDate(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
const today = (() => { const t = new Date(); t.setHours(0, 0, 0, 0); return t; })();

// Placeholder portrait generated as SVG (swap data-avatar for real <img src="photo.jpg">)
function avatar(name){
  const ini=name.replace(/^(Late |Adv\. |Md\. )+/,'').split(' ').map(w=>w[0]).slice(0,2).join('');
  const svg=`<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#1c3a66"/><stop offset="1" stop-color="#0c1e3a"/></linearGradient></defs><rect width="200" height="200" fill="url(#g)"/><circle cx="100" cy="78" r="34" fill="#b8935a" opacity=".35"/><path d="M30 200c6-48 40-64 70-64s64 16 70 64z" fill="#b8935a" opacity=".35"/><text x="100" y="116" font-family="Georgia,serif" font-size="52" fill="#d4b480" text-anchor="middle">${ini}</text></svg>`;
  return 'data:image/svg+xml;utf8,'+encodeURIComponent(svg);
}

/* Page numbers with ellipses: 1 … 4 5 6 … 12 */
function pageItems(cur, total) {
  const set = new Set([1, total, cur - 1, cur, cur + 1].filter(p => p >= 1 && p <= total));
  const out = []; let prev = 0;
  [...set].sort((a, b) => a - b).forEach(p => { if (p - prev > 1) out.push('…'); out.push(p); prev = p; });
  return out;
}
function pagerHTML(page, pages) {
  if (pages <= 1) return '';
  const num = p => p === '…'
    ? '<li class="page-item disabled"><span class="page-link">…</span></li>'
    : `<li class="page-item ${p === page ? 'active' : ''}"><button class="page-link" data-page="${p}" ${p === page ? 'aria-current="page"' : ''}>${p}</button></li>`;
  return `<ul class="pagination justify-content-center flex-wrap">
    <li class="page-item ${page === 1 ? 'disabled' : ''}"><button class="page-link" data-page="${page - 1}" ${page === 1 ? 'disabled' : ''} aria-label="Previous"><i class="bi bi-chevron-left"></i></button></li>
    ${pageItems(page, pages).map(num).join('')}
    <li class="page-item ${page === pages ? 'disabled' : ''}"><button class="page-link" data-page="${page + 1}" ${page === pages ? 'disabled' : ''} aria-label="Next"><i class="bi bi-chevron-right"></i></button></li></ul>`;
}

/* ==========================================================================
   4. COMPONENTS
   ========================================================================== */
const withRole = names => names.map((n, i) => [n, roles[i] || 'Executive Member']);

const personCard = ([n, r]) => `<div class="person"><img src="${avatar(n)}" alt="${esc(n)}"><h5>${esc(n)}</h5><p>${esc(r)}</p></div>`;
const memberCard = ([n, r]) => `<div class="col-6 col-md-4 col-lg-3"><article class="member"><img src="${avatar(n)}" alt="${esc(n)}"><h5>${esc(n)}</h5><p>${esc(r)}</p></article></div>`;

const noticeRow = ([t, d, f]) => `
 <div class="notice"><div class="date"><b>${parseDate(d).toLocaleDateString('en-GB', { day: '2-digit' })}</b><span>${parseDate(d).toLocaleDateString('en-GB', { month: 'short', year: 'numeric' })}</span></div>
 <a class="t" href="#" data-pdf="${esc(f)}" data-title="${esc(t)}">${esc(t)}</a>
 <button class="pdf-btn" data-pdf="${esc(f)}" data-title="${esc(t)}" aria-label="Open PDF: ${esc(t)}"><i class="bi bi-file-earmark-pdf-fill"></i><span class="d-none d-sm-inline">View</span></button></div>`;

const isPast = e => parseDate(e.date) < today;
/* upcoming first (soonest), then past (most recent first); keeps original index as .i */
const sortEvents = list => list.map((e, i) => ({ ...e, i }))
  .sort((a, b) => isPast(a) - isPast(b) || (isPast(a) ? parseDate(b.date) - parseDate(a.date) : parseDate(a.date) - parseDate(b.date)));

/* home = true: the button links to events.html instead of opening the registration modal */
function eventCard(e, home) {
  const p = isPast(e);
  const eventsUrl = $('eventList')?.dataset.eventsUrl || '#events';
  const btn = home
    ? `<a class="btn ${p ? 'btn-outline-gold' : 'btn-gold'} mt-auto align-self-start" href="${eventsUrl}">${p ? 'View details' : '<i class="bi bi-pencil-square me-1"></i>Register Now'}</a>`
    : p ? '<button class="btn btn-outline-gold mt-auto align-self-start" disabled>Registration closed</button>'
        : `<button class="btn btn-gold mt-auto align-self-start" data-reg="${e.i}"><i class="bi bi-pencil-square me-1"></i>Register Now</button>`;
  return `
   <div class="col-md-6 col-lg-4"><article class="event ${p ? 'past' : ''}">
    <div class="top"><b><i class="bi bi-calendar-event me-2"></i>${fmtLong(e.date)}</b><span class="tag">${p ? 'Completed' : e.type}</span></div>
    <div class="body"><h4>${e.title}</h4>
     <p class="meta mb-1"><i class="bi bi-clock me-2"></i>${e.time}</p>
     <p class="meta"><i class="bi bi-geo-alt me-2"></i>${e.venue}</p>
     <p>${e.desc}</p>${btn}</div></article></div>`;
}

const articleUrl = () => document.body.dataset.articleUrl || '/article';
const articlesUrl = () => document.body.dataset.articlesUrl || '/articles';

const articleCard = a => `
 <div class="col-md-6 col-lg-4"><article class="article"><div class="thumb"><i class="bi ${a.icon}"></i></div>
  <div class="body"><span class="tag-cat">${a.cat}</span>
   <span class="by mt-1">${fmtLong(a.date)} · ${authors[a.author].name}</span>
   <h4><a href="${articleUrl()}?id=${encodeURIComponent(a.id)}">${a.title}</a></h4>
   <p class="excerpt">${a.excerpt}</p>
   <a class="btn btn-outline-gold btn-sm mt-auto align-self-start" href="${articleUrl()}?id=${encodeURIComponent(a.id)}">Read More <i class="bi bi-arrow-right ms-1"></i></a>
  </div></article></div>`;

/* Auto-sliding, scroll-snap carousel: loops, pauses on hover, arrow buttons */
function initSlider(id, list) {
  const root = $(id); if (!root) return;
  const track = root.querySelector('.track');
  track.innerHTML = list.map(personCard).join('');
  const step = () => track.firstElementChild.getBoundingClientRect().width + 20;
  const next = () => track.scrollLeft + step() >= track.scrollWidth - track.clientWidth - 4
    ? track.scrollTo({ left: 0, behavior: 'smooth' }) : track.scrollBy({ left: step(), behavior: 'smooth' });
  const prev = () => track.scrollLeft <= 4
    ? track.scrollTo({ left: track.scrollWidth, behavior: 'smooth' }) : track.scrollBy({ left: -step(), behavior: 'smooth' });
  root.querySelector('.r').addEventListener('click', next);
  root.querySelector('.l').addEventListener('click', prev);
  let timer = setInterval(next, 3200);
  root.addEventListener('mouseenter', () => clearInterval(timer));
  root.addEventListener('mouseleave', () => { timer = setInterval(next, 3200); });
}

/* PDF preview modal (home + notices pages) */
function initPdfModal() {
  const el = $('pdfModal'); if (!el || !window.bootstrap) return;
  const modal = new bootstrap.Modal(el);
  document.addEventListener('click', e => {
    const b = e.target.closest('[data-pdf]'); if (!b) return;
    e.preventDefault();
    $('pdfTitle').textContent = b.dataset.title;
    $('pdfFrame').src = b.dataset.pdf;
    $('pdfDl').href = b.dataset.pdf;
    modal.show();
  });
  el.addEventListener('hidden.bs.modal', () => { $('pdfFrame').src = ''; });
}

/* ==========================================================================
   5. PAGES
   ========================================================================== */
const pages = {

  /* ---------- index.html ---------- */
  home() {
    const rows = notices.slice(0, 10);   // 5 per column on desktop
    $('noticeList').innerHTML = [rows.slice(0, 5), rows.slice(5)]
      .map(col => `<div class="col-lg-6">${col.map(noticeRow).join('')}</div>`).join('');
    $('eventList').innerHTML = sortEvents(events).slice(0, 3).map(e => eventCard(e, true)).join('');
    $('articleList').innerHTML = articles.slice(0, 3).map(articleCard).join('');
    initSlider('sCommittee', withRole(terms[0].names));
    initSlider('sFounders', founders);
    initSlider('sDigital', digital);
  },

  /* ---------- about.html ---------- */
  about() {
    $('memberGrid').innerHTML = withRole(terms[0].names).map(memberCard).join('');
  },

  /* ---------- committee.html: year pills + dropdown ---------- */
  committee() {
    const label = t => t.present ? `${t.label} (Present)` : t.label;
    $('pills').innerHTML = terms.map(t => `<button class="term-pill" data-term="${t.id}" aria-pressed="false">${t.label}${t.present ? '<small>Present</small>' : ''}</button>`).join('');
    $('ddMenu').innerHTML = terms.map(t => `<li><button class="dropdown-item" data-term="${t.id}">${label(t)}</button></li>`).join('');
    let current = null;

    function draw(t) {
      $('memberGrid').innerHTML = withRole(t.names).map(memberCard).join('');
      $('termTitle').textContent = 'Executive Committee ' + t.label;
      $('termBadge').hidden = !t.present;
      $('termCount').textContent = t.names.length + ' members served in this term.';
      $('ddLabel').textContent = label(t);
      document.querySelectorAll('.term-pill').forEach(b => { const on = b.dataset.term === t.id; b.classList.toggle('active', on); b.setAttribute('aria-pressed', on); });
      document.querySelectorAll('#ddMenu .dropdown-item').forEach(b => b.classList.toggle('active', b.dataset.term === t.id));
    }
    function show(id, animate) {
      const t = terms.find(x => x.id === id) || terms[0];
      if (t === current) return; current = t;
      if (!animate) return draw(t);
      const g = $('memberGrid'); g.classList.add('swap');
      setTimeout(() => { draw(t); g.classList.remove('swap'); }, 220);
    }
    document.addEventListener('click', e => { const b = e.target.closest('[data-term]'); if (b) show(b.dataset.term, true); });
    show(terms[0].id, false);   // always opens on the present committee
  },

  /* ---------- notices.html: search + pagination ---------- */
  notices() {
    const PER_PAGE = 6;
    let page = 1, query = '';
    function render(scroll) {
      const list = notices.filter(n => n[0].toLowerCase().includes(query));
      const pageCount = Math.max(1, Math.ceil(list.length / PER_PAGE));
      page = Math.min(Math.max(page, 1), pageCount);
      const start = (page - 1) * PER_PAGE, slice = list.slice(start, start + PER_PAGE);
      $('count').textContent = list.length ? `Showing ${start + 1}–${start + slice.length} of ${list.length} notices` : '0 notices found';
      $('noticeList').innerHTML = list.length ? slice.map(noticeRow).join('')
        : `<div class="empty"><i class="bi bi-search"></i><p class="mt-2">No notices match “${esc(query)}”.</p><button class="btn btn-outline-gold" id="clearQ">Clear search</button></div>`;
      $('pager').innerHTML = list.length ? pagerHTML(page, pageCount) : '';
      if (scroll) $('noticeList').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    document.addEventListener('click', e => {
      const p = e.target.closest('[data-page]'); if (p) { page = +p.dataset.page; render(true); }
      if (e.target.id === 'clearQ') { $('q').value = ''; query = ''; page = 1; render(false); }
    });
    $('q').addEventListener('input', e => { query = e.target.value.trim().toLowerCase(); page = 1; render(false); });
    render(false);
  },

  /* ---------- events.html: filter + registration modal ---------- */
  events() {
    let filter = 'all';
    const filters = [['all', 'All events'], ['upcoming', 'Upcoming'], ['past', 'Past']];
    const inFilter = (e, k) => k === 'all' || (k === 'past') === isPast(e);

    function drawFilters() {
      $('filters').innerHTML = filters.map(([k, l]) =>
        `<button class="term-pill ${k === filter ? 'active' : ''}" data-filter="${k}" aria-pressed="${k === filter}">${l} (${events.filter(e => inFilter(e, k)).length})</button>`).join('');
    }
    function drawGrid() {
      $('eventGrid').innerHTML = sortEvents(events).filter(e => inFilter(e, filter)).map(e => eventCard(e, false)).join('');
    }

    const regModal = new bootstrap.Modal($('regModal')), form = $('regForm');
    let current = null;
    function openReg(i) {
      current = events[i];
      form.reset(); form.classList.remove('was-validated');
      form.hidden = false; $('regDone').hidden = true;
      $('regTitle').textContent = 'Register for this event';
      $('regSummary').innerHTML = `<b>${current.title}</b><i class="bi bi-calendar-event me-1"></i>${fmtLong(current.date)} &nbsp; <i class="bi bi-clock me-1"></i>${current.time}<br><i class="bi bi-geo-alt me-1"></i>${current.venue}`;
      regModal.show();
    }
    form.addEventListener('submit', e => {
      e.preventDefault();
      form.classList.add('was-validated');
      if (!form.checkValidity()) return;
      /* TODO: send to your backend, e.g.
         fetch('/api/register', { method: 'POST', headers: { 'Content-Type': 'application/json' },
           body: JSON.stringify({ event: current.title, name: $('fName').value, enrolment: $('fId').value,
             mobile: $('fMobile').value, email: $('fEmail').value, address: $('fAddr').value }) }) */
      $('doneName').textContent = $('fName').value.trim();
      $('doneEvent').textContent = current.title;
      $('doneWhen').textContent = `${fmtLong(current.date)}, ${current.time}, ${current.venue}`;
      $('doneRef').textContent = 'Reference: KDBA-' + Math.floor(100000 + Math.random() * 900000);
      form.hidden = true; $('regDone').hidden = false;
      $('regTitle').textContent = 'Registration complete';
    });
    document.addEventListener('click', e => {
      const f = e.target.closest('[data-filter]'); if (f) { filter = f.dataset.filter; drawFilters(); drawGrid(); }
      const r = e.target.closest('[data-reg]'); if (r) openReg(+r.dataset.reg);
    });
    drawFilters(); drawGrid();
  },

  /* ---------- articles.html: grid + pagination ---------- */
  articles() {
    const PER_PAGE = 6;
    let page = 1;
    function render(scroll) {
      const pageCount = Math.max(1, Math.ceil(articles.length / PER_PAGE));
      page = Math.min(Math.max(page, 1), pageCount);
      $('articleGrid').innerHTML = articles.slice((page - 1) * PER_PAGE, page * PER_PAGE).map(articleCard).join('');
      $('pager').innerHTML = pagerHTML(page, pageCount);
      if (scroll) $('articleGrid').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    document.addEventListener('click', e => { const p = e.target.closest('[data-page]'); if (p) { page = +p.dataset.page; render(true); } });
    render(false);
  },

  /* ---------- single-article.html: loads ?id= ---------- */
  article() {
    const id = parseInt(new URLSearchParams(location.search).get('id'), 10);
    const a = articles.find(x => x.id === id);
    const root = $('articleRoot');

    if (!a) {
      document.title = 'Article not found | Khagrachari District Bar Association';
      $('crumbTitle').textContent = 'Not found';
      root.innerHTML = `<div class="empty"><i class="bi bi-journal-x"></i><h3 class="mt-2">We couldn’t find that article</h3>
        <p>The link may be incorrect or the article may have been removed.</p><a class="btn btn-gold" href="${articlesUrl()}"><i class="bi bi-arrow-left me-1"></i>Browse all articles</a></div>`;
      return;
    }
    const au = authors[a.author];
    const words = a.body.replace(/<[^>]+>/g, ' ').split(/\s+/).filter(Boolean).length;
    const mins = Math.max(1, Math.round(words / 200));
    document.title = a.title + ' | Khagrachari District Bar Association';
    $('crumbTitle').textContent = a.title;
    const url = encodeURIComponent(location.href), txt = encodeURIComponent(a.title);

    root.innerHTML = `
     <header class="a-head"><span class="badge-present">${a.cat}</span><h1>${a.title}</h1>
      <div class="a-meta"><img src="${avatar(au.name)}" alt=""><span>By <b>${au.name}</b></span>
       <span><i class="bi bi-calendar-event me-1"></i>${fmtLong(a.date)}</span><span><i class="bi bi-clock me-1"></i>${mins} min read</span></div></header>
     <div class="banner" aria-hidden="true"><i class="bi ${a.icon}"></i></div>
     <div class="prose">${a.body}
       <p class="note">This article offers general information and commentary by a member of the Association. It is not legal advice; please consult an advocate about your specific situation.</p></div>
     <aside class="author-box"><img src="${avatar(au.name)}" alt="${au.name}"><div><h4>${au.name}</h4><div class="role">${au.role}</div><p class="mb-0">${au.bio}</p></div></aside>
     <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div class="share"><span class="lbl">Share:</span>
       <a href="https://www.facebook.com/sharer/sharer.php?u=${url}" target="_blank" rel="noopener" aria-label="Share on Facebook"><i class="bi bi-facebook"></i></a>
       <a href="https://twitter.com/intent/tweet?url=${url}&text=${txt}" target="_blank" rel="noopener" aria-label="Share on X"><i class="bi bi-twitter-x"></i></a>
       <a href="https://www.linkedin.com/sharing/share-offsite/?url=${url}" target="_blank" rel="noopener" aria-label="Share on LinkedIn"><i class="bi bi-linkedin"></i></a>
       <a href="https://wa.me/?text=${txt}%20${url}" target="_blank" rel="noopener" aria-label="Share on WhatsApp"><i class="bi bi-whatsapp"></i></a>
       <button id="copyBtn" aria-label="Copy link" title="Copy link"><i class="bi bi-link-45deg"></i></button></div>
      <a class="btn btn-outline-gold" href="${articlesUrl()}"><i class="bi bi-arrow-left me-1"></i>Back to all articles</a></div>`;

    $('copyBtn').addEventListener('click', async () => {
      const b = $('copyBtn');
      try { await navigator.clipboard.writeText(location.href); b.innerHTML = '<i class="bi bi-check2"></i>'; }
      catch (e) { window.prompt('Copy this link:', location.href); }
      setTimeout(() => { b.innerHTML = '<i class="bi bi-link-45deg"></i>'; }, 1800);
    });

    /* related: same category first, then the most recent others */
    const others = articles.filter(x => x.id !== a.id);
    const rel = [...others.filter(x => x.cat === a.cat), ...others.filter(x => x.cat !== a.cat)].slice(0, 3);
    $('relatedGrid').innerHTML = rel.map(articleCard).join('');
    $('relatedSec').hidden = false;
  }
};

/* ==========================================================================
   6. BOOT
   ========================================================================== */
document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  document.querySelectorAll('img[data-avatar]').forEach(i => { i.src = avatar(i.dataset.avatar); });
  const yr = $('yr'); if (yr) yr.textContent = new Date().getFullYear();
  initPdfModal();
  const view = document.body.dataset.view;
  if (pages[view]) pages[view]();
});

})();