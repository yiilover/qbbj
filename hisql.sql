delete from qb_sort where fid<41;
alter table qb_guestbook_content add companyname varchar(255) NOT NULL default '';
alter table qb_guestbook_content add truename varchar(255) NOT NULL default '';
alter table qb_guestbook_content add phone varchar(255) NOT NULL default '';
alter table qb_guestbook_content add deadline varchar(255) NOT NULL default '';
alter table qb_guestbook_content add attach1 varchar(255) NOT NULL default '';
alter table qb_guestbook_content add attach2 varchar(255) NOT NULL default '';
alter table qb_guestbook_content add attach3 varchar(255) NOT NULL default '';
alter table qb_guestbook_content add attachurl varchar(255) NOT NULL default '';
alter table qb_guestbook_content add ofid mediumint(7) NOT NULL DEFAULT '0';
alter table qb_guestbook_content add aid mediumint(7) NOT NULL DEFAULT '0';
alter table qb_guestbook_content add goods_num mediumint(7) NOT NULL DEFAULT '1';
alter table qb_guestbook_content add goods_spe varchar(255) NOT NULL default '';
alter table qb_guestbook_content add goods_remark varchar(255) NOT NULL default '';


alter table qb_article add goods_sn varchar(255) NOT NULL default '';