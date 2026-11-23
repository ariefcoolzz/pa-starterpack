-- Pindahkan paragraf pertama ke introtext agar ringkasan kartu berita terisi.
UPDATE jos_content
SET introtext  = SUBSTRING_INDEX(`fulltext`, '</p>', 1),
    `fulltext` = TRIM(SUBSTRING(`fulltext`, CHAR_LENGTH(SUBSTRING_INDEX(`fulltext`, '</p>', 1)) + 5))
WHERE (introtext = '' OR introtext IS NULL)
  AND `fulltext` LIKE '%</p>%';

SELECT id, alias, CHAR_LENGTH(introtext) AS intro_len, CHAR_LENGTH(`fulltext`) AS body_len
FROM jos_content
WHERE catid IN (12, 13)
ORDER BY id;
