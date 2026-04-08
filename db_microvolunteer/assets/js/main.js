document.addEventListener('DOMContentLoaded', function () {
  const navbar = document.getElementById('navbar');
  if (navbar) { window.addEventListener('scroll', () => { navbar.classList.toggle('scrolled', window.scrollY > 60); }); }
  const reveals = document.querySelectorAll('.reveal');
  if (reveals.length) {
    const obs = new IntersectionObserver((entries) => { entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); } }); }, { threshold: 0.1 });
    reveals.forEach(el => obs.observe(el));
  }
  document.querySelectorAll('.alert-auto').forEach(el => { setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }, 4000); });
});
function openModal(id) { const m = document.getElementById(id); if (m) m.classList.add('show'); }
function closeModal(id) { const m = document.getElementById(id); if (m) m.classList.remove('show'); }
document.addEventListener('click', function (e) { if (e.target.classList.contains('modal-overlay')) { e.target.classList.remove('show'); } });
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { document.querySelectorAll('.modal-overlay.show').forEach(m => m.classList.remove('show')); } });
function togglePass(inputId, btnOrId) { const inp = document.getElementById(inputId); if (!inp) return; const isPass = inp.type === 'password'; inp.type = isPass ? 'text' : 'password'; const btn = (typeof btnOrId === 'string') ? document.getElementById(btnOrId) : btnOrId; if (btn) btn.style.opacity = isPass ? '0.45' : '1'; }
function confirmDelete(formId, msg) { if (confirm(msg || 'Are you sure?')) { document.getElementById(formId).submit(); } }
function animateCounters() {
  document.querySelectorAll('[data-count]').forEach(el => {
    const target = +el.dataset.count, suffix = el.dataset.suffix || ''; let current = 0; const step = target / 60;
    const iv = setInterval(() => { current += step; if (current >= target) { current = target; clearInterval(iv); } el.textContent = Math.floor(current).toLocaleString() + suffix; }, 22);
  });
}
function formatDate(dateStr) { const bulan = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'], d = new Date(dateStr); return d.getDate() + ' ' + bulan[d.getMonth()] + ' ' + d.getFullYear(); }
function checkConflict(newDate, newStart, newEnd, existingSchedule) {
  for (const j of existingSchedule) {
    if (j.date !== newDate) continue;
    const nStart = newDate + 'T' + newStart, nEnd = newDate + 'T' + newEnd, eStart = j.date + 'T' + j.start_time, eEnd = j.date + 'T' + j.end_time;
    if (nStart < eEnd && nEnd > eStart) return j;
  }
  return null;
}
