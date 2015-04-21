<?php
namespace MariaDB;

class MariaDB
{
    private $conn;
    private $dbName;

    public function init($dsn, $username = null, $password = null, $options = array())
    {
        $conn = new \PDO($dsn, $username, $password, $options);
        $res = $conn->query('SHOW VARIABLES LIKE "version"');
	$row = $res->fetch(\PDO::FETCH_NUM);
        if (strpos($row[1], 'MariaDB') !== false) {
            $conn->exec('SET GLOBAL userstat=1');
            $this->dbName = $conn->query('SELECT DATABASE()')->fetchColumn();
            $this->conn = $conn;
        }
    }

    public function statistics($context, &$storage)
    {
        if (!$this->conn) { 
            return;
        }
	$sql = "SELECT CLIENT as Client, TOTAL_CONNECTIONS as 'Total Connections', CONCURRENT_CONNECTIONS as 'Concurrent Connections', CONNECTED_TIME as 'Connected Time', BUSY_TIME as 'Busy Time', CPU_TIME as 'CPU Time', BYTES_RECEIVED as 'Bytes Received', BYTES_SENT as 'Bytes Sent', BINLOG_BYTES_WRITTEN as 'Binlog Bytes Written', ROWS_READ as 'Rows Read', ROWS_SENT as 'Rows Sent', ROWS_DELETED as 'Rows deleted', ROWS_INSERTED as 'Rows Inserted', ROWS_UPDATED as 'Rows updated', SELECT_COMMANDS as 'Select Commands', UPDATE_COMMANDS as 'Update Commands', OTHER_COMMANDS as 'Other Commands' FROM INFORMATION_SCHEMA.CLIENT_STATISTICS";
        foreach ($this->conn->query($sql, \PDO::FETCH_ASSOC) as $row) {
            $client = $row['Client'];
            unset($row['Client']);
            $storage['clientStatistics'][] = array($client => $row); 
        }

        $sql = "SELECT USER AS User, TOTAL_CONNECTIONS AS 'Total Connections', CONCURRENT_CONNECTIONS AS 'Concurrent Connections', CONNECTED_TIME AS 'Connected Time', BUSY_TIME AS 'Busy Time', CPU_TIME AS 'CPU Time', BYTES_RECEIVED As 'Bytes Received', BYTES_SENT AS 'Bytes Sent', BINLOG_BYTES_WRITTEN AS 'Binlog Bytes Written', ROWS_READ AS 'Rows Read', ROWS_SENT AS 'Rows Sent', ROWS_DELETED AS 'Rows Deleted', ROWS_INSERTED AS 'Rows Inserted', ROWS_UPDATED AS 'Rows Updated', SELECT_COMMANDS AS 'Select Commands', UPDATE_COMMANDS AS 'Update Commands', OTHER_COMMANDS AS 'Other Commands', COMMIT_TRANSACTIONS AS 'Commit Transactions', ROLLBACK_TRANSACTIONS AS 'Rollback Transactions', DENIED_CONNECTIONS AS 'Denied Connections', LOST_CONNECTIONS AS 'Lost Connections', ACCESS_DENIED AS 'Access Denied', EMPTY_QUERIES AS 'Empty Queries' FROM INFORMATION_SCHEMA.USER_STATISTICS";

        foreach ($this->conn->query($sql, \PDO::FETCH_ASSOC) as $row) {
            $user = $row['User'];
            unset($row['User']);
            $storage['userStatistics'][] = array($user => $row);
        }

        $sql = "SELECT TABLE_NAME AS 'Table Name', ROWS_READ AS 'Rows Read', ROWS_CHANGED AS 'Rows Changed', ROWS_CHANGED_X_INDEXES AS 'Rows Changed X Indexes' FROM INFORMATION_SCHEMA.TABLE_STATISTICS WHERE TABLE_SCHEMA = '{$this->dbName}'";
        foreach ($this->conn->query($sql, \PDO::FETCH_ASSOC) as $row) {
            $storage['tableStatistics'][] = $row;
        }

        $sql = "SELECT TABLE_NAME AS 'Table Name', INDEX_NAME AS 'Index Name', ROWS_READ as 'Rows Read' FROM INFORMATION_SCHEMA.INDEX_STATISTICS WHERE TABLE_SCHEMA = '{$this->dbName}'";
        foreach ($this->conn->query($sql, \PDO::FETCH_ASSOC) as $row) {
            $storage['indexStatistics'][] = $row;
        }
    }
}
