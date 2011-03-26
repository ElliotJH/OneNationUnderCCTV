import urllib2
import zipfile
import mapnik
import sys


from matplotlib import pyplot as plt
import numpy


class AreaCrimeData:
    name = "West Midlands"
    url = """http://crimemapper2.s3.amazonaws.com/frontend/crime-data/2011-02/2011-02-west-midlands-street.zip"""
    
    def fetchData(self):
        response = urllib2.urlopen(url)
        zipVomit = response.read()
        content = ZipFile(zipVomit)
    def plotData(self, csvData, latRange, lonRange, width, height, guid):
        
        latsAndLongs = [self.convertEastingNorthing(latlon) for latlon in self.cleanData(csvData)]
        
        latsAndLongs2 = [latLon for latLon in latsAndLongs if ((latRange[0] < latLon[0] < latRange[1]) & (lonRange[0] < latLon[1] < lonRange[1]))]
        
        
        x = [latLon[0] for latLon in latsAndLongs2]
        y = [latLon[1] for latLon in latsAndLongs2]
        
        H, xedges, yedges = numpy.histogram2d(x, y, 50)
        extent = [yedges[0], yedges[-1], xedges[-1], xedges[0]]

        plt.imshow(H, extent=extent, interpolation='gaussian')

        plt.axis('off')
        
        plt.savefig(guid + '.png', format='png', alpha=0.5)
    def cleanData(self, csvData):
        longsAndLats = []
        csvData.readline()
        for line in csvData:
            try:
                longsAndLats += [[line.split(',')[3]] + [line.split(',')[4]]]
            except IndexError:
                pass
        return longsAndLats
    def convertEastingNorthing(self, eastingnorthing):
        from mapnik import Projection, Coord
        
        britishProjection = Projection('+proj=tmerc +lat_0=49 +lon_0=-2 +k=0.9996012717 +x_0=400000 +y_0=-100000 +ellps=airy +datum=OSGB36 +units=m +no_defs')
        c = Coord(float(eastingnorthing[0]), float(eastingnorthing[1]))
        c = britishProjection.inverse(c)
        return [c.y,c.x]
def drawProjection(latmin, latmax, lonmin, lonmax, guid):
    acd = AreaCrimeData()
    try:
        acd.plotData(open('2011-02-west-midlands-street.csv'), [latmin,latmax] , [lonmin,lonmax], 0, 0, guid)
    except ValueError:
        sys.exit(-1)

latmin = float(sys.argv[1]) 
latmax = float(sys.argv[2])
lonmin = float(sys.argv[3])
lonmax = float(sys.argv[4])
guid = str(sys.argv[5])

drawProjection(latmin, latmax, lonmin, lonmax, guid)
