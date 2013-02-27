#
# Rules script for sample directory Names data
#
import time
import urllib
import httplib
import java, os

from string import Template
import types

from com.googlecode.fascinator.api.storage import StorageException
from com.googlecode.fascinator.common import JsonObject
from com.googlecode.fascinator.common import JsonSimple
from com.googlecode.fascinator.common.storage import StorageUtils
from com.googlecode.fascinator.common import FascinatorHome
from com.googlecode.fascinator.common.messaging import MessagingServices
from com.googlecode.fascinator.messaging import TransactionManagerQueueConsumer

from java.lang import Exception
from java.lang import String
from java.util import HashSet

from org.apache.commons.io import IOUtils
from org.apache.commons.codec.digest import DigestUtils

class IndexData:
    def __init__(self):
        self.messaging = MessagingServices.getInstance()

    def __activate__(self, context):
        # Prepare variables
        self.index = context["fields"]
        self.indexer = context["indexer"]
        self.object = context["object"]
        self.payload = context["payload"]
        self.params = context["params"]
        self.utils = context["pyUtils"]
        self.config = context["jsonConfig"]
        self.log = context["log"]
        self.redboxVersion = self.config.getString("", "redbox.version.string")

        # Common data
        self.__newDoc()
        self.packagePid = None
        pidList = self.object.getPayloadIdList()
        for pid in pidList:
            if pid.endswith(".tfpackage"):
                self.packagePid = pid

        # Real metadata
        if self.itemType == "object":
            self.__basicData()
            self.__metadata()

        # Make sure security comes after workflows
        self.__security()

    def __newDoc(self):
        self.oid = self.object.getId()
        self.pid = self.payload.getId()
        metadataPid = self.params.getProperty("metaPid", "DC")

        self.utils.add(self.index, "storage_id", self.oid)
        if self.pid == metadataPid:
            self.itemType = "object"
        else:
            self.oid += "/" + self.pid
            self.itemType = "datastream"
            self.utils.add(self.index, "identifier", self.pid)

        self.utils.add(self.index, "id", self.oid)
        self.utils.add(self.index, "item_type", self.itemType)
        self.utils.add(self.index, "last_modified", time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()))
        self.utils.add(self.index, "harvest_config", self.params.getProperty("jsonConfigOid"))
        self.utils.add(self.index, "harvest_rules",  self.params.getProperty("rulesOid"))

        self.item_security = []
        self.owner = self.params.getProperty("owner", "admin")

    def __basicData(self):
        self.utils.add(self.index, "repository_name", self.params["repository.name"])
        self.utils.add(self.index, "repository_type", self.params["repository.type"])
        # Persistent Identifiers
        pidProperty = self.config.getString(None, ["curation", "pidProperty"])
        if pidProperty is None:
            self.log.error("No configuration found for persistent IDs!")
        else:
            pid = self.params[pidProperty]
            if pid is not None:
                self.utils.add(self.index, "known_ids", pid)
                self.utils.add(self.index, "pidProperty", pid)
                self.utils.add(self.index, "oai_identifier", pid)
        self.utils.add(self.index, "oai_set", "Directory_Names")
        # Publication
        published = self.params["published"]
        if published is not None:
            self.utils.add(self.index, "published", "true")

    def __metadata(self):
        self.title = None
        self.dcType = None

        self.__checkMetadataPayload()

        jsonPayload = self.object.getPayload("metadata.json")
        json = self.utils.getJsonObject(jsonPayload.open())
        jsonPayload.close()

        metadata = json.getObject("metadata")

        identifier  = metadata.get("dc.identifier")
        self.utils.add(self.index, "dc:identifier", identifier)
        self.__storeIdentifier(identifier)
        self.utils.add(self.index, "institution", "James Cook University")
        self.utils.add(self.index, "source", "http://spatialecology.jcu.edu.au/Edgar/")

        data = json.getObject("data")

        ####Global setting for processing data
        ####These will need to be changed based on you system installation.
        theMintHost = java.lang.System.getProperty("mint.proxy.url")
        collectionRelationTypesFilePath = FascinatorHome.getPath() + "/../portal/default/redbox/workflows/forms/data/"
        servicesRelationTypesFilePath = FascinatorHome.getPath() + "/../portal/default/redbox/workflows/forms/data/"
        descriptionTypesFilePath = FascinatorHome.getPath() + "/../portal/default/local/workflows/forms/data/"
        relationshipTypesFilePath = FascinatorHome.getPath() + "/../portal/default/local/workflows/forms/data/"

        ###Allocating space to create the formData.tfpackage
        tfpackageData = {}

        # We will do string substitutions on data that we get from the default json.
        # We always replace ${NAME_OF_FOLDER} with the name of the folder; if the
        # override json contains a key "DATA_SUBSTITUTIONS", then we also substitute
        # stuff we find there.

        # so: start with just wanting ${NAME_OF_FOLDER} replaced with the actual directory name
        dirName = data.get("harvest_dir_name")
        replacements = { 'NAME_OF_FOLDER': dirName }

        # is there a DATA_SUBSTITUTIONS key?  If so, add those in.
        additionalReplacements = data.get("DATA_SUBSTITUTIONS")
        if additionalReplacements:
            replacements.update(additionalReplacements)

        # now there's a replacements dictionary with the replacements we want
        # to do on our incoming JSON strings.

        # FANCY PART---------------------------------------------
        # Now it gets a bit fancy: Here's a method that does a
        # get-and-replace all in one go.  That makes the rest of
        # this __metdata() method much simpler and more readable.
        #
        # Because this method is defined inside this __metadata()
        # method, it already knows about the replacements var we
        # just made.

        # dataBucket is the thing that has the data.  key is the
        # name of the field you want to get.
        def getAndReplace(dataBucket, key):
            temp = dataBucket.get(key) # fetch the value
            if isinstance(key, str):   # if it's a string, do our replacements
                return Template(temp).safe_substitute(replacements)
            else:                      # not a string, then just hand it back
                return temp
        # END OF FANCY PART -------------------------------------

        title = getAndReplace(data, "title")
        self.utils.add(self.index, "dc_title", title)
        tfpackageData["dc:title"] = title
        tfpackageData["title"] = title

        self.utils.add(self.index, "dc_type", data.get("type"))
        tfpackageData["dc:type.rdf:PlainLiteral"] = data.get("type")
        tfpackageData["dc:type.skos:prefLabel"] = data.get("type")
        tfpackageData["dc:created"] = time.strftime("%Y-%m-%d", time.gmtime())
        tfpackageData["dc:modified"] = ""
        tfpackageData["dc:language.skos:prefLabel"] = "English"
        tfpackageData["dc:coverage.vivo:DateTimeInterval.vivo:start"] = data.get("temporalCoverage").get("dateFrom")

        dateTo = data.get("temporalCoverage").get("dateTo")
        if dateTo is not None:
            tfpackageData["dc:coverage.vivo:DateTimeInterval.vivo:end"] = dateTo

        tfpackageData["dc:coverage.redbox:timePeriod"] = ""

        ###Processing the 'spatialCoverage' metadata.
        spatialCoverage = data.get("spatialCoverage")
        for i in range(len(spatialCoverage)):
            location = spatialCoverage[i]
            if  location["type"] == "text":
                tfpackageData["dc:coverage.vivo:GeographicLocation." + str(i + 1) + ".dc:type"] = location["type"]
                if  (location["value"].startswith("POLYGON")):
                    tfpackageData["dc:coverage.vivo:GeographicLocation." + str(i + 1) + ".redbox:wktRaw"] = location["value"]
                tfpackageData["dc:coverage.vivo:GeographicLocation." + str(i + 1) + ".rdf:PlainLiteral"] = location["value"]

        ###Processing the 'description' metadata.
        #Reading the file here, so we only do it once.
        file = open(descriptionTypesFilePath + "descriptionTypes.json")
        descriptionData = file.read()
        file.close()
        description = data.get("description")
        for i in range(len(description)):
            desc = description[i]
            tempDesc = getAndReplace(desc, "value")
            if  (desc["type"] == "brief"):
                tfpackageData["dc:description"] = tempDesc
            tfpackageData["rif:description." + str(i + 1) + ".type"] = desc["type"]
            tfpackageData["rif:description." + str(i + 1) + ".value"] = tempDesc
            jsonSimple = JsonSimple(descriptionData)
            jsonObj = jsonSimple.getJsonObject()
            results = jsonObj.get("results")
            #ensuring the Description Type exist
            if  results:
                for j in range(len(results)):
                    descriptionType = results[j]
                    if  (desc["type"] == descriptionType.get("id")):
                        tfpackageData["rif:description." + str(i + 1) + ".label"] = descriptionType.get("label")

        ###Processing the 'relatedPublication' metadata
        relatedPublication = data.get("relatedPublication")
        if relatedPublication is not None:
            for i in range(len(relatedPublication)):
                publication = relatedPublication[i]
                tfpackageData["dc:relation.swrc:Publication." + str(i + 1) + ".dc:identifier"] = publication["doi"]
                tfpackageData["dc:relation.swrc:Publication." + str(i + 1) + ".dc:title"] = publication["title"]

        ###Processing the 'relatedWebsite' metadata
        relatedWebsite = data.get("relatedWebsite")
        count = 0
        for i in range(len(relatedWebsite)):
            website = relatedWebsite[i]
            tfpackageData["dc:relation.bibo:Website." + str(i + 1) + ".dc:identifier"] = getAndReplace(website, "url")
            tfpackageData["dc:relation.bibo:Website." + str(i + 1) + ".dc:title"] = getAndReplace(website, "notes")
            count = i + 1

        ###Processing the 'data_source_website' metadata (override metadata)
        dataSourceWebsites = data.get("data_source_website")
        if  dataSourceWebsites is not None:
            for i in range(len(dataSourceWebsites)):
                website = dataSourceWebsites[i]
                type = website.get("identifier").get("type")
                if type == "uri":
                    count += 1
                    tfpackageData["dc:relation.bibo:Website." + str(count) + ".dc:identifier"] = getAndReplace(website.get("identifier"), "value")
                    tfpackageData["dc:relation.bibo:Website." + str(count) + ".dc:title"] = getAndReplace(website, "notes")

        ###Processing the 'relatedCollection' metadata
        #Reading the file here, so we only do it once.
        file = open(collectionRelationTypesFilePath + "collectionRelationTypes.json")
        collectionData = file.read()
        file.close()
        relatedCollection = data.get("relatedCollection")
        recordIdentifier = ""
        if relatedCollection is not None:
            for i in range(len(relatedCollection)):
                collection = relatedCollection[i]
                tempIdentifier = collection["identifier"]
                if tempIdentifier is not None:
                    tempIdentifier = Template( tempIdentifier ).safe_substitute(replacements)
                    recordIdentifier = tempIdentifier
                else:
                    tempIdentifier = ""
                tfpackageData["dc:relation.vivo:Dataset." + str(i + 1) + ".dc:identifier"] = tempIdentifier
                tempTitle = collection.get("title")
                tempTitle = Template( tempTitle ).safe_substitute(replacements)
                tfpackageData["dc:relation.vivo:Dataset." + str(i + 1) + ".dc:title"] = tempTitle
                tfpackageData["dc:relation.vivo:Dataset." + str(i + 1) + ".vivo:Relationship.rdf:PlainLiteral"] = collection["relationship"]
                if  tempIdentifier == "":
                    tfpackageData["dc:relation.vivo:Dataset." + str(i + 1) + ".redbox:origin"] = "on"
                tfpackageData["dc:relation.vivo:Dataset." + str(i + 1) + ".redbox:publish"] =  "on"
                #Using the collection data as a lookup to obtain the 'label'
                relationShip = collection.get("relationship")
                jsonSimple = JsonSimple(collectionData)
                jsonObj = jsonSimple.getJsonObject()
                results = jsonObj.get("results")
                #ensuring the Collection Relation Types exist
                if  results:
                    for j in range(len(results)):
                        relation = results[j]
                        if  (relationShip == relation.get("id")):
                            tfpackageData["dc:relation.vivo:Dataset." + str(i + 1) + ".vivo:Relationship.skos:prefLabel"] = relation.get("label")

        ###Processing the 'relatedService' metadata
        #Reading the file here, so we only do it once.
        file = open(servicesRelationTypesFilePath + "serviceRelationTypes.json")
        servicesData = file.read()
        file.close()
        relatedServices = data.get("relatedService")
        recordIdentifier = ""
        if relatedServices is not None:
            for i in range(len(relatedServices)):
                service = relatedServices[i]
                tfpackageData["dc:relation.vivo:Service." + str(i + 1) + ".dc:identifier"] = service["identifier"]
                tfpackageData["dc:relation.vivo:Service." + str(i + 1) + ".dc:title"] = service["title"]
                tfpackageData["dc:relation.vivo:Service." + str(i + 1) + ".vivo:Relationship.rdf:PlainLiteral"] = service["relationship"]
                #Using the services data as a lookup to obtain the 'label'
                relationShip = service.get("relationship")
                jsonSimple = JsonSimple(servicesData)
                jsonObj = jsonSimple.getJsonObject()
                results = jsonObj.get("results")
                #ensuring the Service Relation Types exist
                if  results:
                    for j in range(len(results)):
                        relation = results[j]
                        if  (relationShip == relation.get("id")):
                            tfpackageData["dc:relation.vivo:Service." + str(i + 1) + ".vivo:Relationship.skos:prefLabel"] = relation.get("label")

        ###Processing the 'associatedParty' metadata
        #Reading the file here so we only read it once.
        file = open(relationshipTypesFilePath + "relationshipTypes.json")
        relationshipData = file.read()
        file.close()
        associatedParty = data.get("associatedParty")
        for i in range(len(associatedParty)):
            party = associatedParty[i]
            email = party.get("who").get("value")
            if email is not None:
                whoType = party.get("who").get("type")
                if (whoType == 'people'):
                    tfpackageData["dc:creator.foaf:Person." + str(i + 1) + ".dc:identifier"] = party.get("who").get("identifier")
                    tfpackageData["dc:creator.foaf:Person." + str(i + 1) + ".foaf:name"] = party.get("who").get("name")
                    tfpackageData["dc:creator.foaf:Person." + str(i + 1) + ".foaf:title"] = party.get("who").get("title")
                    tfpackageData["dc:creator.foaf:Person." + str(i + 1) + ".redbox:isCoPrimaryInvestigator"] = "off"
                    tfpackageData["dc:creator.foaf:Person." + str(i + 1) + ".redbox:isPrimaryInvestigator"] = "on"
                    tfpackageData["dc:creator.foaf:Person." + str(i + 1) + ".foaf:givenName"] = party.get("who").get("givenName")
                    tfpackageData["dc:creator.foaf:Person." + str(i + 1) + ".foaf:familyName"] = party.get("who").get("familyName")
                    tfpackageData["dc:creator.foaf:Person." + str(i + 1) + ".jcu:relationshipType"] = party.get("relationship")
                    tfpackageData["dc:creator.foaf:Person." + str(i + 1) + ".foaf:Organization.dc:identifier"] = party.get("affiliation").get("id")
                    tfpackageData["dc:creator.foaf:Person." + str(i + 1) + ".foaf:Organization.skos:prefLabel"] = party.get("affiliation").get("label")
                    jsonSimple = JsonSimple(relationshipData)
                    jsonObj = jsonSimple.getJsonObject()
                    results = jsonObj.get("results")
                    #ensuring the Relationship Type exists
                    if  results:
                        for j in range(len(results)):
                            relationshipType = results[j]
                            if  (party.get("relationship") == relationshipType.get("id")):
                                tfpackageData["dc:creator.foaf:Person." + str(i + 1) + ".jcu:relationshipLabel"] = relationshipType.get("label")

        ###Processing 'contactInfo.email' metadata
        tfpackageData["locrel:prc.foaf:Person.dc:identifier"] = data.get("contactInfo").get("identifier")
        tfpackageData["locrel:prc.foaf:Person.foaf:name"] = data.get("contactInfo").get("name")
        tfpackageData["locrel:prc.foaf:Person.foaf:title"] = data.get("contactInfo").get("title")
        tfpackageData["locrel:prc.foaf:Person.foaf:givenName"] = data.get("contactInfo").get("givenName")
        tfpackageData["locrel:prc.foaf:Person.foaf:familyName"] = data.get("contactInfo").get("familyName")
        tfpackageData["locrel:prc.foaf:Person.foaf:email"] = data.get("contactInfo").get("email")

        ##Stored At (on the Data Management page)
        tfpackageData["vivo:Location.vivo:GeographicLocation.gn:name"] = data.get("contactInfo").get("streetAddress")

        ###Processing 'coinvestigators' metadata
        coinvestigators = data.get("coinvestigators")
        for i in range(len(coinvestigators)):
            tfpackageData["dc:contributor.locrel:clb." + str(i + 1) + ".foaf:Agent"] = coinvestigators[i]

        ###Processing 'anzsrcFOR' metadata
        anzsrcFOR = data.get("anzsrcFOR")
        for i in range(len(anzsrcFOR)):
            anzsrc = anzsrcFOR[i]
            tfpackageData["dc:subject.anzsrc:for." + str(i + 1) + ".skos:prefLabel"] = anzsrc.get("prefLabel")
            tfpackageData["dc:subject.anzsrc:for." + str(i + 1) + ".rdf:resource"] = anzsrc.get("resource")

        ###Processing 'anzsrcSEO' metadata
        anzsrcSEO = data.get("anzsrcSEO")
        for i in range(len(anzsrcSEO)):
            anzsrc = anzsrcSEO[i]
            tfpackageData["dc:subject.anzsrc:seo." + str(i + 1) + ".skos:prefLabel"] = anzsrc.get("prefLabel")
            tfpackageData["dc:subject.anzsrc:seo." + str(i + 1) + ".rdf:resource"] = anzsrc.get("resource")

        ###Processing 'keyword' metadata
        keyword = data.get("keyword")
        for i in range(len(keyword)):
            tfpackageData["dc:subject.vivo:keyword." + str(i + 1) + ".rdf:PlainLiteral"] = keyword[i]

        ###Research Themes
        theme = data.get("researchTheme")
        if  (theme == "Tropical Ecosystems, Conservation and Climate Change"):
            tfpackageData["jcu:research.themes.tropicalEcoSystems"] = "true"
        elif (theme == "Industries and Economies in the Tropics"):
            tfpackageData["jcu:research.themes.industriesEconomies"] = "true"
        elif (theme == "People and Societies in the Tropics"):
            tfpackageData["jcu:research.themes.peopleSocieties"] = "true"
        elif (theme == "Tropical Health, Medicine and Biosecurity"):
            tfpackageData["jcu:research.themes.tropicalHealth"] = "true"
        elif (theme == "Not aligned to a University theme"):
            tfpackageData["jcu:research.themes.notAligned"] = "true"

        tfpackageData["dc:accessRights.skos:prefLabel"] = data.get("accessRights")
        tfpackageData["dc:license.dc:identifier"] = data.get("license").get("url")
        tfpackageData["dc:license.skos:prefLabel"] = data.get("license").get("label")

        #identifier
        additionalId = data.get("additionalIdentifier")
        if additionalId is not None:
            additionalId = Template( additionalId ).safe_substitute(replacements)
            tfpackageData["dc:identifier.rdf:PlainLiteral"] = additionalId
            tfpackageData["dc:identifier.redbox:origin"] = "external"
            tfpackageData["dc:identifier.dc:type.rdf:PlainLiteral"] = "local"
            tfpackageData["dc:identifier.dc:type.skos:prefLabel"] = "Local Identifier"
        else:
            tfpackageData["dc:identifier.redbox:origin"] = "internal"

        dataLocation = getAndReplace(data, "dataLocation")
        tfpackageData["bibo:Website.1.dc:identifier"] = dataLocation

        #The following have been intentionally set to blank. No mapping is required for these fields.
        tfpackageData["redbox:retentionPeriod"] = data.get("retentionPeriod")
        tfpackageData["dc:extent"] = "unknown"
        tfpackageData["redbox:disposalDate"] = ""
        tfpackageData["locrel:own.foaf:Agent.1.foaf:name"] = ""
        tfpackageData["locrel:dtm.foaf:Agent.foaf:name"] = ""

        ###Processing 'organizationalGroup' metadata
        organisationalGroup = data.get("organizationalGroup")
        for i in range(len(organisationalGroup)):
            organisation = organisationalGroup[i]
            tfpackageData["foaf:Organization.dc:identifier"] = organisation.get("identifier")
            tfpackageData["foaf:Organization.skos:prefLabel"] = organisation.get("prefLabel")

        tfpackageData["swrc:ResearchProject.dc:title"] = ""
        tfpackageData["locrel:dpt.foaf:Person.foaf:name"] = ""
        tfpackageData["dc:SizeOrDuration"] = ""
        tfpackageData["dc:Policy"] = ""

        #Citations
        citations = data.get("citations")
        for i in range(len(citations)):
            citation = citations[i]
            tfpackageData["dc:biblioGraphicCitation.redbox:sendCitation"] = citation.get("sendCitation")
            tfpackageData["dc:biblioGraphicCitation.dc:hasPart.dc:identifier.skos:note"] = citation.get("curationIdentifier")
            paperTitle = getAndReplace(citation, "paperTitle")
            tfpackageData["dc:biblioGraphicCitation.dc:hasPart.dc:title"] = paperTitle
            tfpackageData["dc:biblioGraphicCitation.dc:hasPart.locrel:ctb." + str(i + 1) + ".foaf:familyName"] = citation.get("familyName")
            tfpackageData["dc:biblioGraphicCitation.dc:hasPart.locrel:ctb." + str(i + 1) + ".foaf:givenName"] = citation.get("givenName")
            tfpackageData["dc:biblioGraphicCitation.dc:hasPart.locrel:ctb." + str(i + 1) + ".foaf:title"] = title = citation.get("title")
            tfpackageData["dc:biblioGraphicCitation.dc:hasPart.dc:publisher.rdf:PlainLiteral"] = getAndReplace(citation, "publisher")
            url = getAndReplace(citation, "url")
            tfpackageData["dc:biblioGraphicCitation.dc:hasPart.bibo:Website.dc:identifier"] = url
            tfpackageData["dc:biblioGraphicCitation.dc:hasPart.dc:date.1.rdf:PlainLiteral"] = tfpackageData["dc:created"]
            tfpackageData["dc:biblioGraphicCitation.dc:hasPart.dc:date.1.dc:type.rdf:PlainLiteral"] = "publicationDate"
            tfpackageData["dc:biblioGraphicCitation.dc:hasPart.dc:date.1.dc:type.skos:prefLabel"] = "Publication Date"
            tfpackageData["dc:biblioGraphicCitation.dc:hasPart.dc:date.2.dc:type.rdf:PlainLiteral"] = "created"
            tfpackageData["dc:biblioGraphicCitation.dc:hasPart.dc:date.2.dc:type.skos:prefLabel"] = "Date Created"
            tfpackageData["dc:biblioGraphicCitation.dc:hasPart.dc:date.2.rdf:PlainLiteral"] = tfpackageData["dc:created"]
            tfpackageData["dc:biblioGraphicCitation.dc:hasPart.jcu:dataType"] = citation.get("dataType")
            tfpackageData["dc:biblioGraphicCitation.skos:prefLabel"] = citation.get("familyName") + ", " + citation.get("givenName") + ". (" + time.strftime("%Y", time.gmtime()) + "). " + paperTitle + ". " + citation.get("publisher") + ". [" + citation.get("dataType") + "]  {ID_WILL_BE_HERE}"

        self.__updateMetadataPayload(tfpackageData)
        self.__workflow()

    def __security(self):
        # Security
        roles = self.utils.getRolesWithAccess(self.oid)
        if roles is not None:
            # For every role currently with access
            for role in roles:
                # Should show up, but during debugging we got a few
                if role != "":
                    if role in self.item_security:
                        # They still have access
                        self.utils.add(self.index, "security_filter", role)
                    else:
                        # Their access has been revoked
                        self.__revokeAccess(role)
            # Now for every role that the new step allows access
            for role in self.item_security:
                if role not in roles:
                    # Grant access if new
                    self.__grantAccess(role)
                    self.utils.add(self.index, "security_filter", role)

        # No existing security
        else:
            if self.item_security is None:
                # Guest access if none provided so far
                self.__grantAccess("guest")
                self.utils.add(self.index, "security_filter", role)
            else:
                # Otherwise use workflow security
                for role in self.item_security:
                    # Grant access if new
                    self.__grantAccess(role)
                    self.utils.add(self.index, "security_filter", role)
        # Ownership
        if self.owner is None:
            self.utils.add(self.index, "owner", "system")
        else:
            self.utils.add(self.index, "owner", self.owner)

    def __grantAccess(self, newRole):
        schema = self.utils.getAccessSchema("derby");
        schema.setRecordId(self.oid)
        schema.set("role", newRole)
        self.utils.setAccessSchema(schema, "derby")

    def __revokeAccess(self, oldRole):
        schema = self.utils.getAccessSchema("derby");
        schema.setRecordId(self.oid)
        schema.set("role", oldRole)
        self.utils.removeAccessSchema(schema, "derby")

    def __indexList(self, name, values):
        for value in values:
            self.utils.add(self.index, name, value)

    def __workflow(self):
        # Workflow data
        WORKFLOW_ID = "dataset"
        wfChanged = False
        workflow_security = []
        self.message_list = None
        stages = self.config.getJsonSimpleList(["stages"])
        #if self.owner == "guest":
        #    pageTitle = "Submission Request"
        #    displayType = "submission-request"
        #    initialStep = 0
        #else:
        #    pageTitle = "Metadata Record"
        #    displayType = "package-dataset"
        #    initialStep = 1

        ## Harvesting straight into the 'Published' stage
        pageTitle = "Metadata Record"
        displayType = "package-dataset"
        #initialStep = 4
        initialStep = 3

        try:
            wfMeta = self.__getJsonPayload("workflow.metadata")
            wfMeta.getJsonObject().put("pageTitle", pageTitle)

            # Are we indexing because of a workflow progression?
            targetStep = wfMeta.getString(None, ["targetStep"])
            if targetStep is not None and targetStep != wfMeta.getString(None, ["step"]):
                wfChanged = True
                # Step change
                wfMeta.getJsonObject().put("step", targetStep)
                wfMeta.getJsonObject().remove("targetStep")
            # This must be a re-index then
            else:
                targetStep = wfMeta.getString(None, ["step"])

            # Security change
            for stage in stages:
                if stage.getString(None, ["name"]) == targetStep:
                    wfMeta.getJsonObject().put("label", stage.getString(None, ["label"]))
                    self.item_security = stage.getStringList(["visibility"])
                    workflow_security = stage.getStringList(["security"])
                    if wfChanged == True:
                        self.message_list = stage.getStringList(["message"])
        except StorageException:
            # No workflow payload, time to create

            initialStage = stages.get(initialStep).getString(None, ["name"])
            wfChanged = True
            wfMeta = JsonSimple()
            wfMetaObj = wfMeta.getJsonObject()
            wfMetaObj.put("id", WORKFLOW_ID)
            wfMetaObj.put("step", initialStage)
            wfMetaObj.put("pageTitle", pageTitle)
            stages = self.config.getJsonSimpleList(["stages"])
            for stage in stages:
                if stage.getString(None, ["name"]) == initialStage:
                    wfMetaObj.put("label", stage.getString(None, ["label"]))
                    self.item_security = stage.getStringList(["visibility"])
                    workflow_security = stage.getStringList(["security"])
                    self.message_list = stage.getStringList(["message"])

        # Has the workflow metadata changed?
        if wfChanged == True:
            inStream = IOUtils.toInputStream(wfMeta.toString(True), "UTF-8")
            try:
                StorageUtils.createOrUpdatePayload(self.object, "workflow.metadata", inStream)
            except StorageException:
                print(" ERROR updating dataset payload")

        # Form processing
        coreFields = ["title", "description", "manifest", "metaList", "relationships", "responses"]
        formData = wfMeta.getObject(["formData"])
        if formData is not None:
            formData = JsonSimple(formData)
            # Core fields
            description = formData.getStringList(["description"])
            if description:
                self.descriptionList = description
            # Non-core fields
            data = formData.getJsonObject()
            for field in data.keySet():
                if field not in coreFields:
                    self.customFields[field] = formData.getStringList([field])

        # Manifest processing (formData not present in wfMeta)
        manifest = self.__getJsonPayload(self.packagePid)
        formTitles = manifest.getStringList(["title"])
        if formTitles:
            for formTitle in formTitles:
                if self.title is None:
                    self.title = formTitle
        self.descriptionList = [manifest.getString("", ["description"])]
        formData = manifest.getJsonObject()
        for field in formData.keySet():
            if field not in coreFields:
                value = formData.get(field)
                if value is not None and value.strip() != "":
                    self.utils.add(self.index, field, value)
                    # We want to sort by date of creation, so it
                    # needs to be indexed as a date (ie. 'date_*')
                    if field == "dc:created":
                        parsedTime = time.strptime(value, "%Y-%m-%d")
                        solrTime = time.strftime("%Y-%m-%dT%H:%M:%SZ", parsedTime)
                        self.utils.add(self.index, "date_created", solrTime)
                    # try to extract some common fields for faceting
                    if field.startswith("dc:") and \
                            not (field.endswith(".dc:identifier.rdf:PlainLiteral") \
                              or field.endswith(".dc:identifier") \
                              or field.endswith(".rdf:resource")):
                        # index dublin core fields for faceting
                        basicField = field.replace("dc:", "dc_")
                        dot = field.find(".")
                        if dot > 0:
                            facetField = basicField[:dot]
                        else:
                            facetField = basicField
                        #print "Indexing DC field '%s':'%s'" % (field, facetField)
                        if facetField == "dc_title":
                            if self.title is None:
                                self.title = value
                        elif facetField == "dc_type":
                            if self.dcType is None:
                                self.dcType = value
                        elif facetField == "dc_creator":
                            if basicField.endswith("foaf_name"):
                                self.utils.add(self.index, "dc_creator", value)
                        else:
                            self.utils.add(self.index, facetField, value)
                        # index keywords for lookup
                        if field.startswith("dc:subject.vivo:keyword."):
                            self.utils.add(self.index, "keywords", value)

        self.utils.add(self.index, "display_type", displayType)

        # Workflow processing
        wfStep = wfMeta.getString(None, ["step"])
        self.utils.add(self.index, "workflow_id", wfMeta.getString(None, ["id"]))
        self.utils.add(self.index, "workflow_step", wfStep)
        self.utils.add(self.index, "workflow_step_label", wfMeta.getString(None, ["label"]))
        for group in workflow_security:
            self.utils.add(self.index, "workflow_security", group)
            if self.owner is not None:
                self.utils.add(self.index, "workflow_security", self.owner)
        # set OAI-PMH status to deleted
        if wfStep == "retired":
            self.utils.add(self.index, "oai_deleted", "true")

    def __getJsonPayload(self, pid):
        payload = self.object.getPayload(pid)
        json = self.utils.getJsonObject(payload.open())
        payload.close()
        return json

    def __storeIdentifier(self, identifier):
        try:
            # Where do we find persistent IDs?
            pidProperty = self.config.getString("persistentId", ["curation", "pidProperty"])
            metadata = self.object.getMetadata()
            storedId = metadata.getProperty(pidProperty)
            if storedId is None:
                metadata.setProperty(pidProperty, identifier)
                # Make sure the indexer triggers a metadata save afterwards
                self.params["objectRequiresClose"] = "true"
        except Exception, e:
            self.log.info("Error storing identifier against object: ", e)

    def __checkMetadataPayload(self):
        try:
            # Simple check for its existance
            self.object.getPayload("formData.tfpackage")
            self.firstHarvest = False
        except Exception:
            self.firstHarvest = True
            # We need to create it
            self.log.info("Creating 'formData.tfpackage' payload for object '{}'", self.oid)
            # Prep data
            data = {
                "viewId": "default",
                "workflow_source": "Edgar Import",
                "packageType": "dataset",
                "redbox:formVersion": self.redboxVersion,
                "redbox:newForm": "true"
            }
            package = JsonSimple(JsonObject(data))
            # Store it
            inStream = IOUtils.toInputStream(package.toString(True), "UTF-8")
            try:
                self.object.createStoredPayload("formData.tfpackage", inStream)
                self.packagePid = "formData.tfpackage"
            except StorageException, e:
                self.log.error("Error creating 'formData.tfpackage' payload for object '{}'", self.oid, e)
                raise Exception("Error creating package payload: ", e)

    def __updateMetadataPayload(self, data):
        # Get and parse
        payload = self.object.getPayload("formData.tfpackage")
        json = JsonSimple(payload.open())
        payload.close()

        # Basic test for a mandatory field
        title = json.getString(None, ["dc:title"])
        if title is not None:
            # We've done this before
            return

        # Merge
        json.getJsonObject().putAll(data)

        # Store it
        inStream = IOUtils.toInputStream(json.toString(True), "UTF-8")
        try:
            self.object.updatePayload("formData.tfpackage", inStream)
        except StorageException, e:
            self.log.error("Error updating 'formData.tfpackage' payload for object '{}'", self.oid, e)

        #this message kicks off the curation process.
        #self.__sendMessage(self.oid, "live")

    # Send an event notification to the curation manager
    def __sendMessage(self, oid, step):
        message = JsonObject()
        message.put("oid", oid)
        if step is None:
            message.put("eventType", "ReIndex")
        else:
            message.put("eventType", "NewStep : %s" % step)
        message.put("newStep", step)
        message.put("username", "admin")
        message.put("context", "Workflow")
        message.put("task", "workflow")
        self.messaging.queueMessage(
                TransactionManagerQueueConsumer.LISTENER_ID,
                message.toString())