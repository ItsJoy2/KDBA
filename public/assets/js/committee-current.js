(function () {
  const COMMITTEE = {
    term: '2026-2027',
    members: [
      { name: 'Adv. Md. Abdul Malek', role: 'President', photo: '/assets/images/committee/2026-2027/adv-abdul-malek-mintu.jpg' },
      { name: 'Adv. Kamal Uddin Majumder', role: 'Senior Vice President' },
      { name: 'Adv. Subhra Deb Tripura', role: 'Vice President' },
      { name: 'Adv. Sheikh Md. Jamal Hossen Siddiky', role: 'General Secretary', photo: '/assets/images/committee/2026-2027/adv-shaikh-md-jamal-hossain-siddiky.jpeg' },
      { name: 'Adv. Md. Mokhlesur Rahman Bhuiyan', role: 'Joint General Secretary' },
      { name: 'Adv. Muhammad Kabir Hossain', role: 'Finance Secretary' },
      { name: 'Adv. Jasim Uddin Khan', role: 'Information & Technology Secretary' },
      { name: 'Adv. Nazrul Islam Sohag', role: 'Library Secretary' },
      { name: 'Adv. Abdullah Al Mamun', role: 'Sports & Cultural Secretary' },
      { name: 'Adv. Gouri Prova Dey', role: 'Education & Scholarship Secretary' },
      { name: 'Adv. Islam Mohammad Merajul Islam Miraj', role: 'Social Welfare & Public Welfare Secretary' },
      { name: 'Adv. Karim Ullah', role: 'Executive Member', photo: '/assets/images/committee/2026-2027/karim-ulla.jpg' },
      { name: 'Adv. Md. Shahjahan', role: 'Executive Member' },
      { name: 'Adv. Abu Sufian Sabuj', role: 'Executive Member' },
      { name: 'Adv. Shahinur Akter Pinky', role: 'Executive Member' }
    ]
  };

  // Chobi na thakle ei placeholder dekhabe
  const PHOTO = '/assets/images/member/default-avatar.jpg';

  const photoOf = m => m.photo || PHOTO;

  const esc = s => String(s).replace(/[&<>"']/g, c =>
    ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  // Home page: existing .person slider card
  function renderHomeSlider() {
    const track = document.querySelector('#sCommittee .track');
    if (!track) return;
    track.innerHTML = COMMITTEE.members.map(m => `
      <div class="person">
        <img src="${photoOf(m)}" alt="${esc(m.name)}" onerror="this.onerror=null;this.src='${PHOTO}'">
        <h5>${esc(m.name)}</h5>
        <p>${esc(m.role)}</p>
      </div>`).join('');
  }

  // Committee archive page: existing .member grid
  function renderArchiveGrid() {
    const grid = document.getElementById('memberGrid');
    if (!grid) return;

    const title = document.getElementById('termTitle');
    const count = document.getElementById('termCount');
    const badge = document.getElementById('termBadge');
    if (title) title.textContent = COMMITTEE.term;
    if (count) count.textContent = `${COMMITTEE.members.length} members`;
    if (badge) badge.hidden = false;

    grid.innerHTML = COMMITTEE.members.map(m => `
      <div class="col-sm-6 col-lg-4 col-xl-3">
        <div class="member">
          <img src="${photoOf(m)}" alt="${esc(m.name)}" onerror="this.onerror=null;this.src='${PHOTO}'">
          <h5>${esc(m.name)}</h5>
          <p>${esc(m.role)}</p>
        </div>
      </div>`).join('');
  }

  document.addEventListener('DOMContentLoaded', () => {
    renderHomeSlider();
    renderArchiveGrid();
  });
})();