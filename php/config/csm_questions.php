<?php
/**
 * config/csm_questions.php
 *
 * Official ARTA CSM Questionnaire v2 (2024) — SQD0-8 and CC1-3 wording.
 * Source: Annex A.i (English), Annex A.ii (Tagalog) — client-provided 2024.
 *
 * NOTE: Tagalog "online" wording is currently identical to "onsite" because
 * the client's Tagalog docx did not include distinct online SQD4/6/7 text
 * (unlike the English version). Flagged to client for a corrected version.
 * Waray-Waray is not yet available — client sent a duplicate Tagalog file
 * by mistake. Add a 'war' key here once the correct file is received.
 *
 * Usage:
 *   $questions = include 'config/csm_questions.php';
 *   $sqd = $questions['sqd'][$lang][$channel];   // array of 9 questions, key sqd0..sqd8
 *   $cc  = $questions['cc'][$lang];               // array of 3 questions, key cc1..cc3
 */

return [

    // ══════════════════════════════════════════
    // SQD0 - SQD8  (Service Quality Dimensions)
    // ══════════════════════════════════════════
    'sqd' => [

        'en' => [
            'onsite' => [
                'sqd0' => "I am satisfied with the service that I availed.",
                'sqd1' => "I spent a reasonable amount of time for my transaction.",
                'sqd2' => "The office followed the transaction's requirements and steps based on the information provided.",
                'sqd3' => "The steps (including payment) I needed to do for my transaction were easy and simple.",
                'sqd4' => "I easily found information about my transaction from the office or its website.",
                'sqd5' => "I paid a reasonable amount of fees for my transaction. (If service was free, mark the 'N/A' column)",
                'sqd6' => "I feel the office was fair to everyone, or \"walang palakasan,\" during my transaction.",
                'sqd7' => "I was treated courteously by the staff, and (if asked for help) the staff was helpful.",
                'sqd8' => "I got what I needed from the government office, or (if denied) denial of request was sufficiently explained to me.",
            ],
            'online' => [
                'sqd0' => "I am satisfied with the service that I availed.",
                'sqd1' => "I spent a reasonable amount of time for my transaction.",
                'sqd2' => "The office followed the transaction's requirements and steps based on the information provided.",
                'sqd3' => "The steps (including payment) I needed to do for my transaction were easy and simple.",
                'sqd4' => "I easily found information about my transaction from the office's website.",
                'sqd5' => "I paid a reasonable amount of fees for my transaction. (If service was free, mark the 'N/A' column)",
                'sqd6' => "I am confident my online transaction was secure.",
                'sqd7' => "The office's online support was available, and (if asked questions) online support was quick to respond.",
                'sqd8' => "I got what I needed from the government office, or (if denied) denial of request was sufficiently explained to me.",
            ],
        ],

        'tl' => [
            // Onsite = official Tagalog wording from client docx
            'onsite' => [
                'sqd0' => "Nasiyahan ako sa serbisyo na aking natanggap sa napuntahan na tanggapan.",
                'sqd1' => "Makatwiran ang oras na aking ginugol para sa pagproseso ng aking transaksyon.",
                'sqd2' => "Ang opisina ay sumusunod sa mga kinakailangang dokumento at mga hakbang batay sa impormasyong ibinigay.",
                'sqd3' => "Ang mga hakbang sa pagproseso, kasama na ang pagbayad ay madali at simple lamang.",
                'sqd4' => "Mabilis at madali akong nakahanap ng impormasyon tungkol sa aking transaksyon mula sa opisina o sa website nito.",
                'sqd5' => "Nagbayad ako ng makatwirang halaga para sa aking transaksyon. (Kung ang sebisyo ay ibinigay ng libre, maglagay ng tsek sa hanay ng N/A.)",
                'sqd6' => "Pakiramdam ko ay patas ang opisina sa lahat, o \"walang palakasan\", sa aking transaksyon.",
                'sqd7' => "Magalang akong trinato ng mga tauhan, at (kung sakali ako ay humingi ng tulong) alam ko na sila ay handang tumulong sa akin.",
                'sqd8' => "Nakuha ko ang kinakailangan ko mula sa tanggapan ng gobyerno, kung tinanggihan man, ito ay sapat na ipinaliwanag sa akin.",
            ],
            // TEMP: same as onsite — client's docx did not provide distinct
            // online wording for Tagalog. Replace sqd4/sqd6/sqd7 once received.
            'online' => [
                'sqd0' => "Nasiyahan ako sa serbisyo na aking natanggap sa napuntahan na tanggapan.",
                'sqd1' => "Makatwiran ang oras na aking ginugol para sa pagproseso ng aking transaksyon.",
                'sqd2' => "Ang opisina ay sumusunod sa mga kinakailangang dokumento at mga hakbang batay sa impormasyong ibinigay.",
                'sqd3' => "Ang mga hakbang sa pagproseso, kasama na ang pagbayad ay madali at simple lamang.",
                'sqd4' => "Mabilis at madali akong nakahanap ng impormasyon tungkol sa aking transaksyon mula sa opisina o sa website nito.", // TODO: confirm TL online wording
                'sqd5' => "Nagbayad ako ng makatwirang halaga para sa aking transaksyon. (Kung ang sebisyo ay ibinigay ng libre, maglagay ng tsek sa hanay ng N/A.)",
                'sqd6' => "Pakiramdam ko ay patas ang opisina sa lahat, o \"walang palakasan\", sa aking transaksyon.", // TODO: confirm TL online wording
                'sqd7' => "Magalang akong trinato ng mga tauhan, at (kung sakali ako ay humingi ng tulong) alam ko na sila ay handang tumulong sa akin.", // TODO: confirm TL online wording
                'sqd8' => "Nakuha ko ang kinakailangan ko mula sa tanggapan ng gobyerno, kung tinanggihan man, ito ay sapat na ipinaliwanag sa akin.",
            ],
        ],
    ],

    // ══════════════════════════════════════════
    // CC1 - CC3  (Citizen's Charter awareness)
    // Same for onsite and online in both languages.
    // ══════════════════════════════════════════
    'cc' => [

        'en' => [
            'cc1' => [
                'question' => "Which of the following best describes your awareness of a CC?",
                'options' => [
                    1 => "I know what a CC is and I saw this office's CC.",
                    2 => "I know what a CC is but I did NOT see this office's CC.",
                    3 => "I learned of the CC only when I saw this office's CC.",
                    4 => "I do not know what a CC is and I did not see one in this office. (Answer 'N/A' on CC2 and CC3)",
                ],
            ],
            'cc2' => [
                'question' => "If aware of CC (answered 1-3 in CC1), would you say that the CC of this office was …?",
                'options' => [
                    1 => "Easy to see",
                    2 => "Somewhat easy to see",
                    3 => "Difficult to see",
                    4 => "Not visible at all",
                    5 => "Not Applicable",
                ],
            ],
            'cc3' => [
                'question' => "If aware of CC (answered codes 1-3 in CC1), how much did the CC help you in your transaction?",
                'options' => [
                    1 => "Helped very much",
                    2 => "Somewhat helped",
                    3 => "Did not help",
                    4 => "Not Applicable",
                ],
            ],
        ],

        'tl' => [
            'cc1' => [
                'question' => "Alin sa mga sumusunod ang naglalarawan sa iyong kaalaman sa CC?",
                'options' => [
                    1 => "Alam ko ang CC at nakita ko ito sa napuntahang opisina.",
                    2 => "Alam ko ang CC pero hindi ko ito nakita sa napuntahang opisina.",
                    3 => "Nalaman ko ang CC nang makita ko ito sa napuntahang opisina.",
                    4 => "Hindi ko alam kung ano ang CC at wala akong nakita sa napuntahang opisina. (Lagyan ng tsek ang 'N/A' sa CC2 at CC3)",
                ],
            ],
            'cc2' => [
                'question' => "Kung alam ang CC (Nag-tsek sa opsyon 1-3 sa CC1), masasabi mo ba na ang CC ng napuntahang opisina ay…",
                'options' => [
                    1 => "Madaling makita",
                    2 => "Medyo madaling makita",
                    3 => "Mahirap makita",
                    4 => "Hindi makita",
                    5 => "Hindi angkop",
                ],
            ],
            'cc3' => [
                'question' => "Kung alam ang CC (nag-tsek sa opsyon 1-3 sa CC1), gaano nakatulong ang CC sa transaksyon mo?",
                'options' => [
                    1 => "Sobrang nakatulong",
                    2 => "Nakatulong naman",
                    3 => "Hindi nakatulong",
                    4 => "Hindi angkop",
                ],
            ],
        ],
    ],

];