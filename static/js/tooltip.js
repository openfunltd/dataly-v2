async function renderTooltipContainer(term ,legislatorName) {
  if (legislatorName === "") { return; }
  data = null;
  data = (data == null) ? await requestLegislatorData(term, legislatorName) : JSON.parse(data);
  $('.tooltip-name').text(data.name).attr('href', `/collection/item/legislator/${term}-${legislatorName}`);
  $('.tooltip-area').text(data.areaName);
  $('.tooltip-img').attr('src', data.imgUrl);
  htmlContent = data.committee.map(function(text) {
    return '<p>' + text + '</p>'
  }).join('');
  $('.tooltip-committee').html(htmlContent);
}

function requestLegislatorData(term, legislatorName) {
  return new Promise((resolve, reject) => {
    const url = `${ly_api_url}/legislator/${term}/${legislatorName}`;
    $.get(`${ly_api_url}/legislator/${term}/${legislatorName}`, function(data) {
      data = data.data;
      name = data.委員姓名;
      areaName = data.選區名稱;
      committee = data.委員會;
      imgUrl = data.照片位址;
      data = {
        'name': name,
        'areaName': areaName,
        'committee': committee,
        'imgUrl': imgUrl,
      }
      resolve(data);
    });
  });
}


const tooltips = Array.from(document.querySelectorAll(".wiki-tooltip"));
const tooltipContainer = document.querySelector(".tooltip-container");

tooltips.forEach((tooltip) => {
  tooltip.addEventListener("mouseenter", async (e) => {
    term = e.target.getAttribute('term');
    legislatorName = e.target.getAttribute('legislator-name');
    await renderTooltipContainer(term, legislatorName);
    tooltipContainer.classList.add("fade-in");
    tooltipContainer.style.left = `${e.pageX}px`;
    tooltipContainer.style.top = `${e.pageY}px`;
  });

  tooltip.addEventListener("mouseleave", (e) => {
    tooltipContainer.classList.remove("fade-in");
  });
});

tooltipContainer.addEventListener('mouseenter', (e) => {
  tooltipContainer.classList.add("fade-in");
})
tooltipContainer.addEventListener('mouseleave', (e) => {
  tooltipContainer.classList.remove("fade-in");
})
