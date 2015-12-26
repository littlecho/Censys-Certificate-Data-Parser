# Censys-Certificate-Data-Parser
The parser is written in PHP and designed for processing data obtained from censys.io. 
The raw data file of full-internet dump of certificates is extremely big but well-formatted in a file with json encoded line by line.
This parser read the file line by line and perform json decode for retrieving specified key and its value.
The retrieved key value is then stored in memory for further couting. After all, a output file is saved for post analysis.
