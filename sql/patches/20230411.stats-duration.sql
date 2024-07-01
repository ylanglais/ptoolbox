CREATE TABLE stats.duration (
    key character varying(50) NOT NULL,
    n integer,
    min double precision,
    avg double precision,
    max double precision
);


insert into db.changelog (action) values ('patch 20230411.stats-duration.sql');
