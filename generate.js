const { promises: fsp } = require("fs");

const html = String.raw;
function* matchAll(str, regexp) {
  while (true) {
    const match = regexp.exec(str);
    if (!match) {
      return;
    }
    yield match;
  }
}

async function run() {
  const sqlDump = await fsp.readFile("./db.sql", "utf8");
  const matcher = /\((?<timestamp>[0-9]+),\s*'(?<title>(?:[^']|'')+)',\s*'(?<links>(?:[^']|'')+)'/g;
  const matches = [...matchAll(sqlDump, matcher)];
  const dateFormatter = Intl.DateTimeFormat("de-DE", {
    year: "numeric",
    month: "numeric",
    day: "numeric",
    hour: "numeric",
    minute: "numeric"
  });
  const output = html`
    <html>
      <head>
        <title>Surma's Linkdump</title>
      </head>
      <body>
        <pre>
    <a href="/">Home</a>
    <table border=0>
    ${matches
          .map(({ groups: { timestamp, title, links } }) => {
            const linkList = links.split("|");
            const parsedTitle = title.replace(
              /\[([^\]]+)\]/g,
              (_, text) =>
                `<a href="${linkList.shift()}" target="_blank">${text}</a>`
            );
            return html`
              <tr>
                <td>
                  <nobr
                    ><input type="radio" />${dateFormatter.format(
                      new Date(parseInt(timestamp) * 1000)
                    )}</nobr
                  >
                </td>
                <td>${parsedTitle}</td>
              </tr>
            `;
          })
          .join("\n")} 
    </table>
    </pre>
      </body>
    </html>
  `;
  await fsp.writeFile("index.html", output, "utf8");
}
run();
