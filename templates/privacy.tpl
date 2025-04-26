{extends file="./layouts/layout.tpl"}

{block name="title"}Datenschutzerklärung{/block}

{block name="content"}
<div class="container my-5">
  <h1>Datenschutzerklärung</h1>
  <p>Stand: April 2025</p>

  <div class="datenschutz-content text-start">
    <div class="datenschutz-section mt-4">
      <h2>1. Verantwortliche Stelle</h2>
      <p>StudyHub GmbH<br>Musterstraße 1<br>12345 Musterstadt<br>Deutschland</p>
      <p>Vertreten durch: Maximilian Mustermann</p>
      <p>Telefon: 01234 123-0<br>
         E-Mail: <a href="mailto:info@studyhub.de">info@studyhub.de</a></p>
    </div>

    <div class="datenschutz-section mt-4">
      <h2>2. Erhebung und Verarbeitung personenbezogener Daten</h2>
      <p>Wir erheben und verarbeiten personenbezogene Daten nur, soweit dies erforderlich ist, um unsere Webseite bereitzustellen und die von Ihnen angeforderten Dienste zu erbringen.</p>

      <h3 class="mt-4">2.1. Server-Logfiles</h3>
      <p>Bei jedem Zugriff auf unsere Webseite werden automatisch Informationen in Server-Logfiles gespeichert:</p>
      <ul>
        <li>Browsertyp und -version</li>
        <li>Betriebssystem</li>
        <li>Referrer-URL</li>
        <li>Hostname des zugreifenden Rechners</li>
        <li>Uhrzeit der Serveranfrage</li>
        <li>anonymisierte IP-Adresse</li>
      </ul>
      <p>Diese Daten dienen statistischen Auswertungen und der Sicherheit.</p>

      <h3 class="mt-4">2.2. Kontaktformular</h3>
      <p>Wenn Sie unser Kontaktformular nutzen, erheben wir die von Ihnen eingegebenen Daten zur Bearbeitung Ihrer Anfrage.</p>

      <h3 class="mt-4">2.3. Cookies</h3>
      <p>Wir verwenden möglicherweise Cookies zur Erleichterung der Nutzung. Sie können die Verwendung von Cookies im Browser deaktivieren.</p>
    </div>

    <div class="datenschutz-section mt-4">
      <h2>3. Ihre Rechte</h2>
      <p>Sie haben folgende Rechte:</p>
      <ul>
        <li>Auskunft (Art. 15 DSGVO)</li>
        <li>Berichtigung (Art. 16 DSGVO)</li>
        <li>Löschung (Art. 17 DSGVO)</li>
        <li>Einschränkung der Verarbeitung (Art. 18 DSGVO)</li>
        <li>Datenübertragbarkeit (Art. 20 DSGVO)</li>
        <li>Widerspruch (Art. 21 DSGVO)</li>
        <li>Beschwerde bei einer Aufsichtsbehörde (Art. 77 DSGVO)</li>
      </ul>
    </div>

    <div class="datenschutz-section mt-4">
      <h2>4. Datensicherheit</h2>
      <p>Wir treffen angemessene technische und organisatorische Maßnahmen zum Schutz Ihrer Daten.</p>
    </div>

    <div class="datenschutz-section mt-4">
      <h2>5. Änderungen dieser Datenschutzerklärung</h2>
      <p>Wir behalten uns vor, diese Erklärung an rechtliche Rahmenbedingungen oder interne Prozesse anzupassen.</p>
    </div>
  </div>
</div>
{/block}
