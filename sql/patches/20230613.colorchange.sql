update param.style set key = 'bleu3' where key = 'bleu2';
insert into param.style values ( 'bleu2' , 'color', '#165872');
insert into param.style values ('menuentry_fg', 'color', 'color(bleu2)');
insert into param.style  values ('input_fg', 'color', 'color(bleu2)');

insert into db.changelog (action) values ('patch 20230613.colorchange.sql');
