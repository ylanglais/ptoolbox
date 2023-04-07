create schema stats;
create table stats.duration (key varchar(50) unique not null, n integer, min float, avg float, max float); 
