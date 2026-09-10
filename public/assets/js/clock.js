// Live Brussels time in the footer. Updated every second; the browser computes
// the Europe/Brussels time whatever the visitor's own timezone is.
const el = document.querySelector('[data-brussels-time]');

if (el) {
  const format = new Intl.DateTimeFormat('en-GB', {
    timeZone: 'Europe/Brussels',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false,
  });

  const tick = () => {
    el.textContent = format.format(new Date());
  };

  tick();
  setInterval(tick, 1000);
}
