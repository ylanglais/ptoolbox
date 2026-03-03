--
-- PostgreSQL database dump
--

-- Dumped from database version 15.4
-- Dumped by pg_dump version 15.4

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: flux; Type: TABLE; Schema: tech; Owner: ylanglais
--

CREATE TABLE tech.flux (
    flux character varying(50) NOT NULL,
    source character varying(20) NOT NULL,
    dest character varying(20) NOT NULL
);


ALTER TABLE tech.flux OWNER TO ylanglais;

--
-- Name: log_import; Type: TABLE; Schema: tech; Owner: ylanglais
--

CREATE TABLE tech.log_import (
    id uuid NOT NULL,
    date_debut timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    date_fin timestamp without time zone,
    flux character varying(50) NOT NULL,
    fichier character varying(50),
    nb_in integer,
    nb_charge integer,
    nb_rejet integer,
    nb_dbl_pur integer,
    nb_dbl_pro integer,
    nb_dbl_eve integer,
    pid integer,
    status character varying(30),
    cmd character varying(100)
);


ALTER TABLE tech.log_import OWNER TO ylanglais;

--
-- Name: rejets; Type: TABLE; Schema: tech; Owner: ylanglais
--

CREATE TABLE tech.rejets (
    id uuid NOT NULL,
    date_rejet timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    flux character varying(50),
    fichier character varying(50),
    reference character varying(50),
    raison character varying(5000),
    id_flux uuid
);


ALTER TABLE tech.rejets OWNER TO ylanglais;

--
-- Name: flux flux_pkey; Type: CONSTRAINT; Schema: tech; Owner: ylanglais
--

ALTER TABLE ONLY tech.flux
    ADD CONSTRAINT flux_pkey PRIMARY KEY (flux);


--
-- Name: log_import log_import_pkey; Type: CONSTRAINT; Schema: tech; Owner: ylanglais
--

ALTER TABLE ONLY tech.log_import
    ADD CONSTRAINT log_import_pkey PRIMARY KEY (id);


--
-- Name: rejets rejets_pkey; Type: CONSTRAINT; Schema: tech; Owner: ylanglais
--

ALTER TABLE ONLY tech.rejets
    ADD CONSTRAINT rejets_pkey PRIMARY KEY (id);


--
-- Name: fki_log_import_fk; Type: INDEX; Schema: tech; Owner: ylanglais
--

CREATE INDEX fki_log_import_fk ON tech.log_import USING btree (flux);


--
-- Name: log_import log_import_fk; Type: FK CONSTRAINT; Schema: tech; Owner: ylanglais
--

ALTER TABLE ONLY tech.log_import
    ADD CONSTRAINT log_import_fk FOREIGN KEY (flux) REFERENCES tech.flux(flux) NOT VALID;


--
-- Name: rejets rejet_fk; Type: FK CONSTRAINT; Schema: tech; Owner: ylanglais
--

ALTER TABLE ONLY tech.rejets
    ADD CONSTRAINT rejet_fk FOREIGN KEY (flux) REFERENCES tech.flux(flux) NOT VALID;


--
-- PostgreSQL database dump complete
--

