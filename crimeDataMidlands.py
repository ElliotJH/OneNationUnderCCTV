import urllib2
import gzip



class AreaCrimeData:
    name = "West Midlands"
    url = """http://crimemapper2.s3.amazonaws.com/frontend/crime-data/2011-02/2011-02-west-midlands-street.zip"""
    
    def fetchData(self):
        response = urllib2.urlopen(url)
        zipVomit = response.read()
        
