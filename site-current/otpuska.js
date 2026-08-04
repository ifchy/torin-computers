const OTPUSKA = `В А Ж Н О !!! НОВО Работно време:  понеделник до петък  8:00 до 16:00 часа`;
// const OTPUSKA = `В А Ж Н О !!! Офисът няма да работи на 04.07.2022`;

function showHolidays() {
  if (OTPUSKA && OTPUSKA.trim().length > 0) {
    const rss = document.getElementById("rssBlock");
    if (rss) {
      rss.style.display = "block";
    }
    const rssContent = document.getElementById("rssContent");
    if (rssContent) {
      rssContent.innerHTML = OTPUSKA;
    }
  }
}